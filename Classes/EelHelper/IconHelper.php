<?php

namespace Garagist\Fontawesome\EelHelper;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Garagist\Fontawesome\Service\IconService;

class IconHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var IconService
     */
    protected $iconService;

    /**
     * Get the icon content
     *
     * @param string|null $style
     * @param string|null $icon
     * @return string|null
     */
    public function file(?string $style, ?string $icon): ?string
    {
        return $this->iconService->file($style, $icon);
    }

    /**
     * Get list of icons
     *
     * @return array
     */
    public function list(): array
    {
        return $this->iconService->list();
    }

    /**
     * Get all metadata of icons
     *
     * @return array
     */
    public function metadata(): array
    {
        return $this->iconService->getMetadata();
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName The name of the method
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
