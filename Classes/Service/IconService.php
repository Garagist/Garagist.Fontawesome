<?php

namespace Garagist\Fontawesome\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Symfony\Component\Yaml\Yaml;
use function array_keys;
use function file_exists;
use function file_get_contents;
use function in_array;
use function preg_replace;
use function sprintf;
use function strtolower;

/**
 * A service for gettings icons
 *
 * @Flow\Scope("singleton")
 * @api
 */
class IconService
{
    /**
     * @Flow\InjectConfiguration(package="Garagist.Fontawesome", path="iconLocation")
     * @var string
     */
    protected $iconLocation;

    /**
     * @Flow\InjectConfiguration(package="Garagist.Fontawesome", path="settingsLocation")
     * @var string
     */
    protected $settingsLocation;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * Get the icon content
     *
     * @param string|null $style
     * @param string|null $icon
     * @return string|null
     */
    public function file(?string $style, ?string $icon): ?string
    {
        if (!isset($style) || !isset($icon)) {
            return null;
        }
        $style = strtolower($style);
        $icon = strtolower($icon);
        $content = $this->getIconContent($style, $icon);
        if (isset($content)) {
            return $content;
        }
        return $this->getIconContent($style, $this->getNameFromAlias($icon));
    }

    /**
     * Get the icon path
     *
     * @param string|null $style
     * @param string|null $icon
     * @return string|null
     */
    public function path(string $style, ?string $icon = null): ?string
    {
        $iconPath = $this->resourcePath($style, $icon);

        if (!isset($iconPath)) {
            return null;
        }
        preg_match('#^resource://([^/]+)/Public/(.*)#', $iconPath, $matches);
        $package = $matches[1];
        $path = $matches[2];
        return $this->resourceManager->getPublicPackageResourceUri(
            $package,
            $path
        );
    }

    /**
     * Get list of icons
     *
     * @return array
     */
    public function list(): array
    {
        return array_keys($this->getMetadata());
    }

    /**
     * Get parsed icon yaml file
     *
     * @return array
     */
    public function getMetadata(): array
    {
        $filePath = sprintf('resource://%s', $this->settingsLocation);
        if (!file_exists($filePath)) {
            return [];
        }
        return Yaml::parseFile($filePath);
    }

    /**
     * Get the name of an icon from an alias
     *
     * @param string $alias
     * @return string|null
     */
    private function getNameFromAlias(string $alias): ?string
    {
        $icons = $this->getMetadata();

        // Try to get the icon with an alias name
        foreach ($icons as $key => $value) {
            if (
                isset($value['aliases']) &&
                isset($value['aliases']['names']) &&
                in_array($alias, $value['aliases']['names'])
            ) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Get the icon resource path
     *
     * @param string|null $style
     * @param string|null $icon
     * @return string|null
     */
    public function resourcePath(string $style, ?string $icon = null): ?string
    {
        if (!isset($style) || !isset($icon)) {
            return null;
        }
        $style = strtolower($style);
        $icon = strtolower($icon);
        $iconPath = sprintf(
            'resource://%s/%s/%s.svg',
            $this->iconLocation,
            $style,
            $icon
        );
        if (!file_exists($iconPath)) {
            return null;
        }
        return $iconPath;
    }

    /**
     * Return the content of an icon
     *
     * @param string $style
     * @param string|null $icon
     * @return string|null
     */
    private function getIconContent(
        string $style,
        ?string $icon = null
    ): ?string {
        $iconPath = $this->resourcePath($style, $icon);

        if (!isset($iconPath)) {
            return null;
        }

        // Get content of file and remove comment
        return preg_replace('/<!--.*?-->/s', '', file_get_contents($iconPath));
    }
}
