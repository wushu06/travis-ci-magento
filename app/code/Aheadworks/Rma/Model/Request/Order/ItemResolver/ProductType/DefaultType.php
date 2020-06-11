<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType;

use Aheadworks\Rma\Model\Request\Order\ItemResolver\ItemResolverInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;

/**
 * Class DefaultType
 *
 * @package Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType
 */
class DefaultType implements ItemResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolveBuyRequest($buyRequest, $orderItem, $requestItem)
    {
        $buyRequest[RequestItemInterface::QTY] = $requestItem->getQty();
        return $buyRequest;
    }

    /**
     * @inheritdoc
     */
    public function resolveOrderItem($orderItem, $parentOrderItem = null)
    {
        return [$orderItem->getItemId(), $orderItem];
    }
}
