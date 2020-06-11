<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer;

use Aheadworks\Rma\Model\UnserializeResolver;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Item;

/**
 * Class Bundle
 *
 * @package Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer
 */
class Bundle extends Configurable
{
    /**
     * @var UnserializeResolver
     */
    private $unserializeResolver;

    /**
     * @param Context $context
     * @param Factory $rendererFactory
     * @param UnserializeResolver $unserializeResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $rendererFactory,
        UnserializeResolver $unserializeResolver,
        array $data = []
    ) {
        parent::__construct($context, $rendererFactory, $data);
        $this->unserializeResolver = $unserializeResolver;
    }

    /**
     * Retrieve option label
     *
     * @param Item $childItem
     * @return string
     */
    public function getOptionLabel($childItem)
    {
        $options = $childItem->getProductOptions();
        $selectedAttributes = isset($options['bundle_selection_attributes'])
            ? $this->unserializeResolver->unserialize($options['bundle_selection_attributes'])
            : null;

        return isset($selectedAttributes['option_label']) ? $selectedAttributes['option_label'] : '';
    }

    /**
     * Check if fixed price in bundle
     *
     * @return bool
     */
    public function isFixedPrice()
    {
        // For dynamic bundle
        if ($this->getItem()->getChildrenItems() && $this->getItem()->isChildrenCalculated()) {
            return false;
        }

        return true;
    }
}
