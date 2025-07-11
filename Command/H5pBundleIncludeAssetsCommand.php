<?php

namespace Studit\H5PBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class H5pBundleIncludeAssetsCommand extends Command
{
    protected static $defaultName = 'h5p-bundle:IncludeAssetsCommand';
    /** KernelInterface $appKernel */
    private KernelInterface $appKernel;

    /**
     * @param KernelInterface $appKernel
     */
    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Include the assets from the h5p vendor bundle in the public resources directory of this bundle.'
            )
            ->addOption('copy', 'c', InputOption::VALUE_NONE, 'Copy files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->includeAssets($input->getOption('copy') ?? false);
        return Command::SUCCESS;
    }

    private function includeAssets(bool $copy): void
    {
        $projectDir = $this->appKernel->getProjectDir();

        //get dir of vendor H5P
        $fromDir = $projectDir . "/vendor/h5p/";

        //call service
        $toDir = $projectDir . '/public/bundles/studith5p/h5p/';

        $coreSubDir = "h5p-core/";
        $coreDirs = ["fonts", "images", "js", "styles"];
        $this->createFiles($fromDir, $toDir, $coreSubDir, $coreDirs, $copy);

        $editorSubDir = "h5p-editor/";
        $editorDirs = ["ckeditor", "images", "language", "libs", "scripts", "styles"];
        $this->createFiles($fromDir, $toDir, $editorSubDir, $editorDirs, $copy);
    }

    private function createFiles(string $fromDir, string $toDir, string $subDir, array $subDirs, bool $copy): void
    {
        foreach ($subDirs as $dir) {
            $src = $fromDir . $subDir . $dir;
            $dist = $toDir . $subDir . $dir;

            if ($copy) {
                $this->recurseCopy($src, $dist);
            } else {
                // Create the parent directory if it doesn't exist
                $parentDir = dirname($dist);
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0750, true);
                }

                // Calculate relative path from $dist to $src
                $relativePath = $this->getRelativePath($dist, $src);

                // Create relative symlink
                symlink($relativePath, $dist);
            }
        }
    }

    /**
     * Calculate the relative path from target to source
     */
    private function getRelativePath(string $from, string $to): string
    {
        // Normalize paths
        $from = rtrim($from, '/');
        $to = rtrim($to, '/');

        // Get absolute paths
        $fromParts = explode('/', $from);
        $toParts = explode('/', $to);

        // Remove common path parts
        $i = 0;
        while (isset($fromParts[$i]) && isset($toParts[$i]) && $fromParts[$i] === $toParts[$i]) {
            $i++;
        }

        // Build relative path
        $relativeParts = [];

        // Add .. for each remaining part in $from path (excluding the file/directory name itself)
        for ($j = $i; $j < count($fromParts) - 1; $j++) {
            $relativeParts[] = '..';
        }

        // Add remaining parts from $to path
        for ($j = $i; $j < count($toParts); $j++) {
            $relativeParts[] = $toParts[$j];
        }

        return implode('/', $relativeParts);
    }

    private function recurseCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        // Restrict the permission to 0750 not upper
        @mkdir($dst, 0750, true);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}