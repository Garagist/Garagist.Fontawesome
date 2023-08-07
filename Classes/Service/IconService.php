<?php

namespace Garagist\Fontawesome\Service;

use Neos\Flow\Annotations as Flow;
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
        $filePath = sprintf('resource://%s/icons.yml', $this->iconLocation);
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
            if (isset($value['aliases']) && isset($value['aliases']['names']) && in_array($alias, $value['aliases']['names'])) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Return the content of an icon
     *
     * @param string $style
     * @param string|null $icon
     * @return string|null
     */
    private function getIconContent(string $style, ?string $icon = null): ?string
    {
        if (!isset($icon)) {
            return null;
        }
        $iconPath = sprintf('resource://%s/%s/%s.svg', $this->iconLocation, $style, $icon);
        if (!file_exists($iconPath)) {
            return null;
        }

        // Get content of file and remove comment
        return preg_replace('/<!--.*?-->/s', '', file_get_contents($iconPath));
    }
}
