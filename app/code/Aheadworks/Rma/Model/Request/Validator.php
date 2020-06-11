<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aheadworks\Rma\Model\Request\Order as RequestOrder;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;

/**
 * Class Validator
 *
 * @package Aheadworks\Rma\Model\Request
 */
class Validator extends AbstractValidator
{
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
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestOrder $requestOrder
     * @param RequestOrderItem $requestOrderItem
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RequestOrder $requestOrder,
        RequestOrderItem $requestOrderItem
    ) {
        $this->orderRepository = $orderRepository;
        $this->requestOrder = $requestOrder;
        $this->requestOrderItem = $requestOrderItem;
    }

    /**
     * Returns true if and only RMA request is valid for processing
     *
     * @param RequestInterface $request
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isValid($request)
    {
        $this->_clearMessages();

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($request->getOrderId());
        } catch (NoSuchEntityException $e) {
            $this->_addMessages([_('Incorrect order id.')]);
        }
        if (empty($request->getOrderItems())) {
            $this->_addMessages([__('No item(s) for request specified.')]);
        }
        if (empty($request->getPrintLabel())) {
            $this->_addMessages([__('No Print Label for request specified.')]);
        }

        // If guest mode
        if (!$request->getCustomerId() && $request->getCustomerEmail()) {
            if ($order->getCustomerEmail() != $request->getCustomerEmail()) {
                $this->_addMessages([__('You are not owner of the given order.')]);
            } // If customer mode
        } elseif ($request->getCustomerId() && $request->getCustomerEmail()) {
            if ($order->getCustomerId() != $request->getCustomerId()) {
                $this->_addMessages([__('Customer isn\'t owner of the given order.')]);
            }
        } else {
            $this->_addMessages([__('Incorrect customer data.')]);
        }

        if (!$request->getId() && !$this->requestOrder->isAllowedForOrder($order)) {
            $this->_addMessages([__('You can\'t request RMA for the given order.')]);
        }

        $orderItems = $order->getItemsCollection();
        /** @var RequestItemInterface $requestOrderItem */
        foreach ($request->getOrderItems() as $requestOrderItem) {
            $matchedOrderItem = false;
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getId() == $requestOrderItem->getItemId()) {
                    $matchedOrderItem = $orderItem;
                    break;
                }
            }
            if ($matchedOrderItem) {
                $maxItemAvailable = $this->requestOrderItem->getItemMaxCount($matchedOrderItem, $request->getId());
                if ($requestOrderItem->getQty() < 0
                    || $requestOrderItem->getQty() > $maxItemAvailable
                ) {
                    $this->_addMessages([__('Wrong quantity for %1.', $matchedOrderItem->getName())]);
                }
            } else {
                $this->_addMessages(
                    [
                        __(
                            'Order item %1 does not belong to order #%2.',
                            $requestOrderItem->getItemId(),
                            $order->getIncrementId()
                        )
                    ]
                );
            }
        }

        return empty($this->getMessages());
    }
}
