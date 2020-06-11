<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\ItemResolver;

use Magento\Sales\Api\Data\OrderItemInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;

/**
 * Interface ItemResolverInterface
 *
 * @package Aheadworks\Rma\Model\Request\Order\ItemResolver
 */
interface ItemResolverInterface
{
    /**
     * Resolve buy request
     *
     * @param array $buyRequest
     * @param OrderItemInterface $orderItem
     * @param RequestItemInterface $requestItem
     * @return array
     */
    public function resolveBuyRequest($buyRequest, $orderItem, $requestItem);

    /**
     * Resolve order item ID depending on type
     *
     * @param OrderItemInterface $orderItem
     * @param OrderItemInterface|null $parentOrderItem
     * @return array with order ID and Order Item
     */
    public function resolveOrderItem($orderItem, $parentOrderItem = null);
}
