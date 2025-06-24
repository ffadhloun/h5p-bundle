<?php

namespace Studit\H5PBundle\Twig;

use Studit\H5PBundle\Core\H5PIntegration;
use Studit\H5PBundle\Entity\Content;
use Twig\TwigFilter;

class H5PExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * @var H5PIntegration
     */
    private $h5pIntegration;

    /**
     * H5PExtension constructor.
     * @param H5PIntegration $h5pIntegration
     */
    public function __construct(H5PIntegration $h5pIntegration)
    {
        $this->h5pIntegration = $h5pIntegration;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('h5pCacheBuster', [$this, 'getH5PCacheBuster']),
            new TwigFilter('contentTitle', [$this, 'getContentTitle']),
        ];

    }

    public function getH5PCacheBuster($script): string
    {
        return $script . $this->h5pIntegration->getCacheBuster();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'h5p_extension';
    }

    public function getContentTitle(Content $content): ?string
    {
        $parameters = json_decode($content->getParameters(),true);
        return isset($parameters['metadata']['title']) && is_string($parameters['metadata']['title'])
            ? $parameters['metadata']['title']
            : null;
    }

}
