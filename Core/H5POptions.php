<?php

namespace Studit\H5PBundle\Core;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;
use Studit\H5PBundle\Entity\Option;

class H5POptions
{
    /**
     * @var array
     */
    private array|null $config;

    /**
     * @var array
     */
    private array|null $storedConfig = null;

    private $h5pPath;
    private $folderPath;
    private $projectRootDir;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;

    /**
     * H5POptions constructor.
     * @param array|null $config
     * @param $projectRootDir
     * @param EntityManagerInterface $manager
     */
    public function __construct(?array $config, $projectRootDir, EntityManagerInterface $manager)
    {
        $this->config = $config;
        $this->projectRootDir = $projectRootDir;
        $this->manager = $manager;
    }

    /**
     * Retrieves the value of a configuration option.
     *
     * This method fetches the specified configuration option's value. If the option is found in the cached
     * `storedConfig`, it returns that value. If not, it checks the local `config` array. If the option is
     * not found in either, it returns the provided default value.
     *
     * @param string $name The name of the configuration option.
     * @param mixed $default The default value to return if the option is not found in either `storedConfig` or `config`.
     *
     * @return mixed|null The value of the configuration option, or the default value if the option is not set.
     */
    public function getOption($name, $default = null)
    {
        try {
            $this->retrieveStoredConfig();
        } catch (DriverException) {
            // Suppress database errors and continue
        }

        if (isset($this->storedConfig[$name])) {
            return $this->storedConfig[$name];
        }
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
        return $default;
    }

    /**
     * Sets or updates a configuration option in the database.
     *
     * This method updates the value of a specified configuration option. If the option already exists
     * and its current value differs from the provided value, the method updates it. If the option does
     * not exist, it creates a new one. Changes are persisted to the database.
     *
     * @param string $name The name of the configuration option.
     * @param string|int|null $value The value to set for the configuration option.
     *
     * @throws InvalidArgumentException If the provided option name or value is invalid.
     *
     * @return void
     */
    public function setOption(string $name, string|int|null $value): void
    {
        $this->retrieveStoredConfig();

        if (!isset($this->storedConfig[$name]) || $this->storedConfig[$name] !== $value) {
            $this->storedConfig[$name] = $value;
            $option = $this->manager->getRepository('Studit\H5PBundle\Entity\Option')->find($name);
            if (!$option) {
                $option = new Option($name);
            }
            $option->setValue($value);
            $this->manager->persist($option);
            $this->manager->flush();
        }
    }

    /**
     * Retrieves and caches configuration options from the database.
     *
     * This method loads all configuration options from the database if they haven't been loaded yet.
     * The options are stored as key-value pairs in the `$storedConfig` property for easy access.
     * If `storedConfig` is already populated, the method does nothing to avoid redundant database queries.
     *
     * @return void
     */
    private function retrieveStoredConfig(): void
    {
        if ($this->storedConfig === null) {
            $this->storedConfig = [];
            $options = $this->manager->getRepository('Studit\H5PBundle\Entity\Option')->findAll();
            if (!empty($options)) {
                foreach ($options as $option) {
                    $this->storedConfig[$option->getName()] = $option->getValue();
                }
            }
        }
    }

    /**
     * @param $set
     * @return mixed
     */
    public function getUploadedH5pFolderPath($set = null)
    {
        if (!empty($set)) {
            $this->folderPath = $set;
        }

        return $this->folderPath;
    }

    /**
     * @param $set
     * @return mixed
     */
    public function getUploadedH5pPath($set = null)
    {
        if (!empty($set)) {
            $this->h5pPath = $set;
        }

        return $this->h5pPath;
    }

    /**
     * Helper function to ensure storage_dir always starts with a '/'.
     * Fixed: Now properly handles string values instead of arrays
     *
     * @return string
     */
    private function formatStorageDir(): string
    {
        // Get the storage directory, default to 'h5p' if not set
        $dir = $this->getOption('storage_dir', 'h5p');

        // Ensure it's a string
        if (is_array($dir)) {
            $dir = !empty($dir[0]) ? $dir[0] : 'h5p';
        }

        // Ensure it starts with a forward slash
        return (string)$dir[0] === '/' ? $dir : "/{$dir}";
    }

    /**
     * @return string
     */
    public function getRelativeH5PPath(): string
    {
        return $this->formatStorageDir();
    }

    /**
     * Get absolute H5P path with trailing slash (for directory operations)
     * Fixed: Now correctly points to public directory
     *
     * @return string
     */
    public function getAbsoluteH5PPathWithSlash(): string
    {
        return $this->getPublicDirectoryPath() . $this->formatStorageDir() . '/';
    }

    /**
     * Get absolute H5P path without trailing slash
     * Fixed: Now correctly points to public directory
     *
     * @return string
     */
    public function getAbsoluteH5PPath(): string
    {
        return $this->getPublicDirectoryPath() . $this->formatStorageDir();
    }

    /**
     * Get the absolute path to the public directory
     * This replaces getAbsoluteWebPath() to be more explicit
     *
     * @return string
     */
    public function getPublicDirectoryPath(): string
    {
        return rtrim($this->projectRootDir, '/') . '/public';
    }

    /**
     * @deprecated Use getPublicDirectoryPath() instead
     * Kept for backward compatibility but now correctly points to public directory
     */
    public function getAbsoluteWebPath(): string
    {
        return $this->getPublicDirectoryPath();
    }

    public function getLibraryFileUrl(string $libraryFolderName, string $fileName): string
    {
        return $this->getRelativeH5PPath() . "/libraries/$libraryFolderName/$fileName";
    }

    public function getH5PAssetPath(): string
    {
        return '/bundles/studith5p/h5p';
    }
}