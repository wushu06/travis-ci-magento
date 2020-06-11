<?php

namespace Elementary\Banner\Ui\Component\Listing\Column;

use Elementary\Banner\Api\Data\BannerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Banner Actions Column
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class BannerActions extends Column
{
    /**
     * Url Interface
     *
     * @var UrlInterface
     */
    protected $url;

    /**
     * Banner Actions constructor
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $url
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $url,
        array              $components = [],
        array              $data = []
    ) {
        $this->url = $url;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * This method will modify the actions column to add options to modify files
     *
     * @param array $dataSource Row Data
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $data = [];

        if (!isset($dataSource['data']['items'])) {
            return $data;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $name = $this->getData('name');
            if (!isset($item[BannerInterface::BANNER_ID])) {
                continue;
            }

            $item[$name]['edit'] = [
                'href'  => $this->url->getUrl('banner/banner/edit', [
                    BannerInterface::BANNER_ID => $item[BannerInterface::BANNER_ID]
                ]),
                'label' => __('Edit'),
            ];

            $item[$name]['delete'] = [
                'href'    => $this->url->getUrl('banner/banner/delete', [
                    BannerInterface::BANNER_ID => $item[BannerInterface::BANNER_ID]
                ]),
                'label'   => __('Delete'),
                'confirm' => [
                    'title'   => __('Delete Banner?'),
                    'message' => __('Are you sure you would like to delete this banner?'),
                ]
            ];

        }

        return $dataSource;
    }
}
