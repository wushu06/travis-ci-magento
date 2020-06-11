<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\Config\Cms;

use Magento\Framework\Option\ArrayInterface;
use Magento\Cms\Model\ResourceModel\Block\Collection as BlockCollection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;

/**
 * Class Block
 *
 * @package Aheadworks\Rma\Model\Source\Config\Cms
 */
class Block implements ArrayInterface
{
    /**
     * @var int
     */
    const DONT_DISPLAY = -1;

    /**
     * @var BlockCollection
     */
    private $blockCollection;

    /**
     * @var array
     */
    private $options;

    /**
     * @param BlockCollectionFactory $blockCollectionFactory
     */
    public function __construct(
        BlockCollectionFactory $blockCollectionFactory
    ) {
        $this->blockCollection = $blockCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = array_merge(
                [self::DONT_DISPLAY => __('Don\'t display')],
                $this->blockCollection->toOptionArray()
            );
        }

        return $this->options;
    }
}
