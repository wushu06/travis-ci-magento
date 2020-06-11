<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\ItemResolver;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\Finder as ItemFinder;

/**
 * Class RequestItemList
 *
 * @package Aheadworks\Rma\Model\Request\Order\ItemResolver
 */
class RequestItemList
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var ActionValidator
     */
    private $actionValidator;

    /**
     * @var ItemFinder
     */
    private $itemFinder;

    /**
     * @param Pool $pool
     * @param ActionValidator $actionValidator
     * @param Finder $itemFinder
     */
    public function __construct(
        Pool $pool,
        ActionValidator $actionValidator,
        ItemFinder $itemFinder
    ) {
        $this->pool = $pool;
        $this->actionValidator = $actionValidator;
        $this->itemFinder = $itemFinder;
    }

    /**
     * Retrieve resolved request items for replacement
     *
     * @param RequestInterface $request
     * @param OrderInterface $order
     * @param string $action
     * @return array
     * @throws LocalizedException
     */
    public function getForReplacement($request, $order, $action)
    {
        $resultItems = [];
        $requestItems = $request->getOrderItems();
        foreach ($requestItems as $requestItem) {
            if (!$this->actionValidator->isValidForRequestItem($requestItem, $request, $action)) {
                continue;
            }
            $orderItem = $this->itemFinder->findOrderItem($order, $requestItem->getItemId());
            if ($orderItem->getParentItemId()) {
                $parentItem = $orderItem->getParentItem();
                list ($buyRequest, $itemId, $productId, $productType) = $this->getOrderItemData($parentItem);
            } else {
                list ($buyRequest, $itemId, $productId, $productType) = $this->getOrderItemData($orderItem);
            }
            if (isset($resultItems[$itemId])) {
                $buyRequest = array_values($resultItems[$itemId]);
                $buyRequest = reset($buyRequest);
            }
            $itemResolver = $this->pool->getItemResolver($productType);
            $buyRequest = $itemResolver->resolveBuyRequest($buyRequest, $orderItem, $requestItem);
            $resultItems[$itemId] = [$productId => $buyRequest];
        }

        return $resultItems;
    }

    /**
     * Retrieve resolved request items for creditMemo
     *
     * @param RequestInterface $request
     * @param OrderInterface $order
     * @param string $action
     * @return array
     * @throws LocalizedException
     */
    public function getForCreditMemo($request, $order, $action)
    {
        $resultItems = [];
        $orderItems = $order->getItems();
        foreach ($orderItems as $orderItem) {
            $parentItem = null;
            if ($orderItem->getParentItemId()) {
                $parentItem = $orderItem->getParentItem();
                $productType = $parentItem->getProductType();
            } else {
                $productType = $orderItem->getProductType();
            }
            $itemResolver = $this->pool->getItemResolver($productType);
            list($resolverOrderItemId, $resolverOrderItem) = $itemResolver->resolveOrderItem($orderItem, $parentItem);
            $requestItem = $this->itemFinder->findRequestItem($request, $resolverOrderItem->getItemId());
            if ($requestItem) {
                $qtyToRefund = $this->actionValidator->isValidForRequestItem($requestItem, $request, $action)
                    ? $requestItem->getQty()
                    : 0;

                $resultItems[$resolverOrderItemId] = [RequestItemInterface::QTY => $qtyToRefund];
            } else {
                $resultItems[$resolverOrderItemId] = [RequestItemInterface::QTY => 0];
            }
        }

        return $resultItems;
    }

    /**
     * Get order item data
     *
     * @param OrderItemInterface $orderItem
     * @return array
     */
    private function getOrderItemData($orderItem)
    {
        $buyRequest = $orderItem->getBuyRequest()->getData();
        $itemId = $orderItem->getItemId();
        $productId = $orderItem->getProductId();
        $productType = $orderItem->getProductType();
        return [$buyRequest, $itemId, $productId, $productType];
    }
}
