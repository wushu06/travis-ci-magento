<?php

namespace Elementary\Banner\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Elementary\Banner\Model;
use Elementary\Banner\Model\ResourceModel\Banner\Collection;
use Elementary\Banner\Model\ResourceModel\Banner\CollectionFactory;

/**
 * Banner Source Model
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Banner implements ArrayInterface
{
    /**
     * Banner Collection Factory
     *
     * @var CollectionFactory
     */
    protected $_bannerCollection;

    /**
     * Banner constructor.
     *
     * @param CollectionFactory $bannerCollection
     */
    public function __construct(
        CollectionFactory $bannerCollection
    ) {
        $this->_bannerCollection = $bannerCollection;
    }

    /**
     * Banner Options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = [
            'value' => '',
            'label' => __('Please Select a Banner')
        ];
        /** @var Collection $banners */
        $banners = $this->_bannerCollection->create();
        $banners->addFieldToFilter(Model\Banner::STATUS, [
            'eq' => 1
        ]);
        /** @var Model\Banner $banner */
        foreach ($banners as $banner) {
            $options[] = [
                'value' => $banner->getId(),
                'label' => $banner->getIdentifier()
            ];
        }

        return $options;
    }
}
