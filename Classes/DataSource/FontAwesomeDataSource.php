<?php

namespace Garagist\Fontawesome\DataSource;

use Neos\Flow\Annotations as Flow;
use Garagist\Fontawesome\Service\IconService;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\Neos\Domain\Service\UserService;
use Sandstorm\LazyDataSource\LazyDataSourceTrait;

class FontAwesomeDataSource extends AbstractDataSource
{
    use LazyDataSourceTrait;

    /**
     * @var string
     */
    protected static $identifier = 'garagist-fontawesome';

    /**
     * @Flow\Inject
     * @var IconService
     */
    protected $iconService;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\InjectConfiguration(package="Garagist.Fontawesome", path="styles")
     * @var string
     */
    protected $styles;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $enabledStyles = [];

    /**
     * @param array $identifiers
     * @param mixed $node
     * @param array $arguments
     * @return void
     */
    protected function getDataForIdentifiers(
        array $identifiers,
        mixed $node = null,
        array $arguments = []
    ) {
        $metadata = $this->iconService->getMetadata();
        $this->enabledStyles = $this->getEnabledStyles($arguments);

        foreach ($metadata as $name => $data) {
            $this->addDefaultAndSharpOptions($name, $data);
        }

        return $this->options;
    }

    /**
     * @param string $searchTerm
     * @param mixed $node
     * @param array $arguments
     * @return void
     */
    protected function searchData(
        string $searchTerm,
        mixed $node = null,
        array $arguments = []
    ) {
        $metadata = $this->iconService->getMetadata();
        $searchPreset = $arguments['searchPreset'] ?? null;
        if ($searchPreset) {
            $searchTerm = $searchTerm . ' ' . $searchPreset;
        }
        $searchTerms = array_filter(explode(' ', strtolower($searchTerm)));
        $numSearchTerms = count($searchTerms);

        $this->enabledStyles = $this->getEnabledStyles($arguments);

        // First, we look if the text is in the label
        foreach ($metadata as $name => $data) {
            if (
                $this->filterByTerms(
                    $searchTerms,
                    $numSearchTerms,
                    $data['label']
                )
            ) {
                $this->addDefaultAndSharpOptions($name, $data);
            }
        }

        // Now we look also into the search terms
        foreach ($metadata as $name => $data) {
            $haystack = implode(' ', $data['search']['terms']);
            if (
                $this->filterByTerms($searchTerms, $numSearchTerms, $haystack)
            ) {
                $this->addDefaultAndSharpOptions($name, $data);
            }
        }

        return $this->options;
    }

    /**
     * Get enabled styles splited in default, sharp and groups
     *
     * @param array $arguments
     * @return array
     */
    protected function getEnabledStyles(array $arguments): array
    {
        $defaultAndSharpStyles = $arguments['styles'] ?? $this->styles;
        $enableGroup = count($defaultAndSharpStyles) > 1;
        $enabledStyles = [
            'default' => [],
            'sharp' => [],
            'groups' => [],
        ];
        foreach ($defaultAndSharpStyles as $style) {
            $enabledStyles['groups'][$style] = $enableGroup
                ? str_replace('-', ' ', ucwords($style, '-'))
                : null;
            if (str_starts_with($style, 'sharp-')) {
                $key = str_replace('sharp-', '', $style);
                $enabledStyles['sharp'][$key] = $style;
                continue;
            }
            $enabledStyles['default'][$style] = $style;
        }
        return $enabledStyles;
    }

    /**
     * Filter by search terms
     *
     * @param array $searchTerms
     * @param integer $length
     * @param string $haystack
     * @return boolean
     */
    private function filterByTerms(
        array $searchTerms,
        int $length,
        string $haystack
    ): bool {
        $foundIcons = 0;
        foreach ($searchTerms as $value) {
            if (stripos($haystack, $value) !== false) {
                $foundIcons++;
            }
        }
        return $foundIcons === $length;
    }

    /**
     * Add options for the sharp and default icons to the options array
     *
     * @param string $name
     * @param array $data
     * @return void
     */
    private function addDefaultAndSharpOptions(string $name, array $data)
    {
        $label = $data['label'];
        foreach ($data['styles'] as $style) {
            // Check for default styles
            if (array_key_exists($style, $this->enabledStyles['default'])) {
                $this->addOption(false, $style, $name, $label);
            }

            // Check for sharp styles
            if (array_key_exists($style, $this->enabledStyles['sharp'])) {
                $this->addOption(true, $style, $name, $label);
            }
        }
    }

    /**
     * Add option to the options array
     *
     * @param string $style
     * @param string $name
     * @param array $data
     * @return void
     */
    private function addOption(
        bool $sharp,
        string $style,
        string $name,
        ?string $label = null
    ) {
        if ($sharp) {
            $style = 'sharp-' . $style;
        }
        $value = $style . ':' . $name;

        // Is the icon already in the list?
        if (isset($this->options[$value])) {
            return;
        }

        $this->options[$value] = [
            'label' => $label ?? $name,
            'group' => $this->enabledStyles['groups'][$style],
            'preview' => $this->iconService->path($style, $name),
        ];
    }
}
