<?php

namespace Garagist\Fontawesome\DataSource;

use Neos\ContentRepository\Domain\Model\NodeInterface;
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

    const STYLES = [
        'solid',
        'regular',
        'light',
        'thin',
        'sharp-solid',
        'sharp-regular',
        'sharp-light',
        'duotone',
        'brands',
    ];


    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array $identifiers
     * @param NodeInterface|null $node
     * @param array $arguments
     * @return void
     */
    protected function getDataForIdentifiers(array $identifiers, NodeInterface $node = null, array $arguments = [])
    {
        $this->options = [];
        $enabledStyles = $arguments['styles'] ?? self::STYLES;
        $enableGroup = count($enabledStyles) > 1;
        $metadata = $this->iconService->getMetadata();

        foreach ($metadata as $name => $data) {
            foreach ($data['styles'] as $style) {
                if (!in_array($style, $enabledStyles)) {
                    continue;
                }
                $this->addOption($style, $name, $data, $enableGroup);
            }
        }

        return $this->options;
    }

    /**
     * @param string $searchTerm
     * @param NodeInterface|null $node
     * @param array $arguments
     * @return void
     */
    protected function searchData(string $searchTerm, NodeInterface $node = null, array $arguments = [])
    {
        $this->options = [];
        $enabledStyles = $arguments['styles'] ?? self::STYLES;
        $enableGroup = count($enabledStyles) > 1;
        $metadata = $this->iconService->getMetadata();

        // First, we look if the text is in the label
        foreach ($metadata as $name => $data) {
            foreach ($data['styles'] as $style) {
                if (!in_array($style, $enabledStyles)) {
                    continue;
                }

                if (stripos($data['label'], $searchTerm) === false) {
                    continue;
                }

                $this->addOption($style, $name, $data, $enableGroup);
            }
        }

        // New we look also into the search terms
        foreach ($metadata as $name => $data) {
            foreach ($data['styles'] as $style) {
                if (!in_array($style, $enabledStyles)) {
                    continue;
                }

                // Is the icon already in the list?
                if (isset($options[$style . ':' . $name])) {
                    continue;
                }

                $haystack = implode(' ', $data['search']['terms']);
                if (stripos($haystack, $searchTerm) === false) {
                    continue;
                }

                $this->addOption($style, $name, $data, $enableGroup);
            }
        }

        return $this->options;
    }

    /**
     * Add option to the options array
     *
     * @param string $style
     * @param string $name
     * @param boolean $enableGroup
     * @return void
     */
    private function addOption(string $style, string $name, array $data, bool $enableGroup)
    {
        $value = $style . ':' . $name;

        // Is the icon already in the list?
        if (isset($options[$value])) {
            return;
        }

        $this->options[$value] = [
            'label' => $data['label'],
            'group' => $enableGroup ? ucfirst($style) : null,
            'preview' => $this->previewSvg($style, $name),
        ];
    }


    /**
     * Get svg for the preview
     *
     * @param string $style
     * @param string $name
     * @return void
     */
    private function previewSvg(string $style, string $name)
    {
        $markup = $this->iconService->file($style, $name);
        return 'data:image/svg+xml;base64,' . base64_encode($markup);
    }
}
