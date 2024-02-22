<?php

namespace Garagist\Fontawesome\Controller;

use Garagist\Fontawesome\Service\IconService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

/**
 * @Flow\Scope("singleton")
 */
class IconController extends ActionController
{
    /**
     * @var array
     */
    protected $supportedMediaTypes = ['image/svg+xml'];

    /**
     * @var IconService
     * @Flow\Inject
     */
    protected $iconService;

    /**
     * Return content of icon
     *
     * @param string $style
     * @param string $icon
     * @return string
     */
    public function indexAction(string $style, string $icon): string
    {
        return $this->iconService->file($style, $icon) ?: '';
    }
}
