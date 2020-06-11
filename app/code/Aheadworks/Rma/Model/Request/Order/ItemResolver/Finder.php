<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\ItemResolver;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;

/**
 * Class Finder
 *
 * @package Aheadworks\Rma\Model\Request\Order\ItemResolver
 */
class Finder
{
    /**
     * Find order item
     *
     * @param OrderInterface $order
     * @param int $requestItemId
     * @return  OrderItemInterface
     */
    public function findOrderItem($order, $requestItemId)
    {
        $filterResult = array_filter(
            $order->getItems(),
            function (OrderItemInterface $orderItem) use ($requestItemId) {
                return $orderItem->getId() == $requestItemId;
            }
        );

        return reset($filterResult);
    }

    /**
     * Find request item
     *
     * @param RequestInterface $request
     * @param int $orderItemId
     * @return  RequestItemInterface
     */
    public function findRequestItem($request, $orderItemId)
    {
        $filterResult = array_filter(
            $request->getOrderItems(),
            function (RequestItemInterface $requestItem) use ($orderItemId) {
                return $requestItem->getItemId() == $orderItemId;
            }
        );

        return reset($filterResult);
    }
}
