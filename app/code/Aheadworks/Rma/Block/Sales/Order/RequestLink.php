<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Sales\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Rma\Model\Request\Order as RequestOrder;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class RequestLink
 *
 * @package Aheadworks\Rma\Block\Sales\Order
 */
class RequestLink extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::sales/order/requestlink.phtml';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var RequestOrder
     */
    private $requestOrder;

    /**
     * @var RequestOrderItem
     */
    private $requestOrderItem;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestOrder $requestOrder
     * @param RequestOrderItem $requestOrderItem
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        RequestOrder $requestOrder,
        RequestOrderItem $requestOrderItem,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->requestOrder = $requestOrder;
        $this->requestOrderItem = $requestOrderItem;
    }

    /**
     * Check if can return
     *
     * @return bool
     */
    public function canReturn()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        if (!$order) {
            return false;
        }
        if (!$this->requestOrder->isAllowedForOrder($order)) {
            return false;
        }

        $orderItems = $this->requestOrderItem->getOrderItemsToRequest($order->getEntityId());
        foreach ($orderItems as $orderItem) {
            if ($this->requestOrderItem->getItemMaxCount($orderItem)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve action url
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('aw_rma/customer/new', ['order_id' => $this->getOrder()->getEntityId()]);
    }

    /**
     * Retrieve order
     *
     * @return OrderInterface|bool
     */
    private function getOrder()
    {
        $orderId = $this->_request->getParam('order_id');
        try {
            return $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
