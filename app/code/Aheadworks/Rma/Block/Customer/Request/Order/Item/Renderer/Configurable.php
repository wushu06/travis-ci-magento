<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Item;

/**
 * Class Configurable
 *
 * @method Item getItem()
 * @method int getRequestStatus()
 * @method CustomFieldInterface[] getCustomFields()
 * @package Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer
 */
class Configurable extends Template
{
    /**
     * @var Factory
     */
    private $rendererFactory;

    /**
     * @param Context $context
     * @param Factory $rendererFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $rendererFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rendererFactory = $rendererFactory;
    }

    /**
     * Check if can render
     *
     * @return bool
     */
    public function canRender()
    {
        if (empty($this->getItem())) {
            return false;
        }

        return true;
    }

    /**
     * Child item renderer
     *
     * @param Item $item
     * @param bool $renderDefault
     * @return string
     */
    public function childItemRenderer(Item $item, $renderDefault = false)
    {
        $block = $this->rendererFactory->create($item, $this->getRequestStatus(), $renderDefault);

        return $block->toHtml();
    }
}
