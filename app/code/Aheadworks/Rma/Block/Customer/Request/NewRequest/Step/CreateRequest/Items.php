<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\NewRequest\Step\CreateRequest;

use Aheadworks\Rma\Model\Source\CustomField\EditAt;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer\Factory as ItemRendererFactory;

/**
 * Class Items
 *
 * @package Aheadworks\Rma\Block\Customer\Request\NewRequest\Step\CreateRequest
 */
class Items extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/newrequest/step/createrequest/items.phtml';

    /**
     * @var RequestOrderItem
     */
    private $requestOrderItem;

    /**
     * @var ItemRendererFactory
     */
    private $itemRendererFactory;

    /**
     * @param Context $context
     * @param RequestOrderItem $requestOrderItem
     * @param ItemRendererFactory $itemRendererFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestOrderItem $requestOrderItem,
        ItemRendererFactory $itemRendererFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->requestOrderItem = $requestOrderItem;
        $this->itemRendererFactory = $itemRendererFactory;
    }

    /**
     * Retrieve request order items
     *
     * @return \Magento\Sales\Model\Order\Item[]|\Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getOrderItemsToRequest()
    {
        return $this->requestOrderItem->getParentOrderItemsToRequest($this->getOrderId());
    }

    /**
     * Get item renderer html
     *
     * @param OrderItem $orderItem
     * @return string
     */
    public function getItemRendererHtml(OrderItem $orderItem)
    {
        $block = $this->itemRendererFactory->create($orderItem, EditAt::NEW_REQUEST_PAGE);
        return $block->toHtml();
    }

    /**
     * Retrieve order id
     *
     * @return int
     */
    private function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }
}
