<?php

namespace Studit\H5PBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Studit\H5PBundle\Core\H5PIntegration;
use Studit\H5PBundle\Core\H5POptions;
use Studit\H5PBundle\Editor\LibraryStorage;
use Studit\H5PBundle\Entity\Content;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class H5PContentService
{
    protected H5PIntegration $h5PIntegrations;
    protected LibraryStorage $libraryStorage;
    protected EntityManagerInterface $entityManager;
    protected \H5PCore $h5PCore;
    protected H5POptions $h5POptions;

    public function __construct(
        H5PIntegration $h5PIntegration,
        LibraryStorage $libraryStorage,
        EntityManagerInterface $entityManager,
        \H5PCore $h5PCore,
        H5POptions $h5POptions
    ) {
        $this->h5PIntegrations = $h5PIntegration;
        $this->libraryStorage = $libraryStorage;
        $this->entityManager = $entityManager;
        $this->h5PCore = $h5PCore;
        $this->h5POptions = $h5POptions;
    }

    public function initContent(Content $content): array
    {
        $h5pIntegration = $this->h5PIntegrations->getGenericH5PIntegrationSettings();
        $contentIdStr = 'cid-' . $content->getId();
        $h5pIntegration['contents'][$contentIdStr] = $this->h5PIntegrations->getH5PContentIntegrationSettings($content);
        $preloaded_dependencies = $this->h5PCore->loadContentDependencies($content->getId(), 'preloaded');
        $files = $this->h5PCore->getDependenciesFiles($preloaded_dependencies, $this->h5POptions->getRelativeH5PPath());
        if ($content->getLibrary()->isFrame()) {
            $jsFilePaths = array_map(function ($asset) {
                return $asset->path;
            }, $files['scripts']);
            $cssFilePaths = array_map(function ($asset) {
                return $asset->path;
            }, $files['styles']);
            $coreAssets = $this->h5PIntegrations->getCoreAssets();
            $h5pIntegration['core']['scripts'] = $coreAssets['scripts'];
            $h5pIntegration['core']['styles'] = $coreAssets['styles'];
            $h5pIntegration['contents'][$contentIdStr]['scripts'] = $jsFilePaths;
            $h5pIntegration['contents'][$contentIdStr]['styles'] = $cssFilePaths;
        }
        return [
            'contentId' => $content->getId(),
            'isFrame' => $content->getLibrary()->isFrame(),
            'h5pIntegration' => $h5pIntegration,
            'files' => $files,
        ];
    }
}