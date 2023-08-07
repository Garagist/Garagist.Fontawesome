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
     * @param array $identifiers
     * @param NodeInterface|null $node
     * @param array $arguments
     * @return void
     */
    protected function getDataForIdentifiers(array $identifiers, NodeInterface $node = null, array $arguments = [])
    {
        $options = [];
        $enabledStyles = $arguments['styles'] ?? self::STYLES;
        $enableGroup = count($enabledStyles) > 1;
        $metadata = $this->iconService->getMetadata();

        foreach ($metadata as $name => $data) {
            foreach ($data['styles'] as $style) {
                if (!in_array($style, $enabledStyles)) {
                    continue;
                }
                $options[sprintf('%s__%s', $style, $name)] = [
                    'value' => sprintf('%s:%s', $style, $name),
                    'label' => $data['label'],
                    'group' => $enableGroup ? ucfirst($style) : null,
                    'preview' => $this->previewSvg($style, $name),
                ];
            }
        }

        return $options;
    }

    /**
     * @param string $searchTerm
     * @param NodeInterface|null $node
     * @param array $arguments
     * @return void
     */
    protected function searchData(string $searchTerm, NodeInterface $node = null, array $arguments = [])
    {
        $options = [];
        $enabledStyles = $arguments['styles'] ?? self::STYLES;
        $enableGroup = count($enabledStyles) > 1;
        $metadata = $this->iconService->getMetadata();

        foreach ($metadata as $name => $data) {
            foreach ($data['styles'] as $style) {
                if (!in_array($style, $enabledStyles)) {
                    continue;
                }
                $haystack = $data['label'] . ' ' . implode(' ', $data['search']['terms']);

                if (stripos($haystack, $searchTerm) === false) {
                    continue;
                }

                $options[sprintf('%s__%s', $style, $name)] = [
                    'value' => sprintf('%s:%s', $style, $name),
                    'label' => $data['label'],
                    'group' => $enableGroup ? ucfirst($style) : null,
                    'preview' => $this->previewSvg($style, $name),
                ];
            }
        }


        // $options = [];
        // $options['key'] = ['label' => 'My Label ' . $searchTerm];
        return $options;
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
