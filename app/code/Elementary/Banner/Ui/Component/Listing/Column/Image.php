<?php

namespace Elementary\Banner\Ui\Component\Listing\Column;

use Elementary\Banner\Helper\Data;
use Elementary\Banner\Model\Slide;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Slide Image Column
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Image extends Column
{
    /**
     * Config Scope
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Url
     *
     * @var UrlInterface
     */
    protected $_url;

    /**
     * Asset Repository
     *
     * @var Repository
     */
    protected $_assetRepository;

    /**
     * Banner Helper
     *
     * @var Data
     */
    protected $_helper;

    /**
     * Image constructor
     *
     * @param ContextInterface     $context
     * @param UiComponentFactory   $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface         $url
     * @param Repository           $assetRepository
     * @param Data                 $helper
     * @param array                $components
     * @param array                $data
     */
    public function __construct(
        ContextInterface     $context,
        UiComponentFactory   $uiComponentFactory,
        ScopeConfigInterface $scopeConfig,
        UrlInterface         $url,
        Repository           $assetRepository,
        Data                 $helper,
        array                $components = [],
        array                $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_scopeConfig = $scopeConfig;
        $this->_url = $url;
        $this->_assetRepository = $assetRepository;
        $this->_helper = $helper;
    }

    /**
     * This method will set the data for the image column
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $data = [];

        if (!isset($dataSource['data']['items'])) {
            return $data;
        }

        $columnName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $imageUrl = null;
            if (isset($item[$columnName])) {
                $image = $item[$columnName];
                if ($image) {
                    $imageUrl = $this->_helper->getSlideImageUrl($image);
                }
            }

            if (!$imageUrl) {
                $imageUrl = $this->_assetRepository->getUrl('Magento_Catalog::images/product/placeholder/image.jpg');
            }

            $item[$columnName . '_src'] = $imageUrl;
            $item[$columnName . '_orig_src'] = $imageUrl;
            $item[$columnName . '_alt'] = __('%1 Slide', $item[Slide::TITLE]);
            $item[$columnName . '_link'] = $this->_url->getUrl('banner/slide/edit', [
                'slide_id' => $item[Slide::SLIDE_ID]
            ]);
        }

        return $dataSource;
    }
}
