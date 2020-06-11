<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType;

use Aheadworks\Rma\Model\Request\Order\ItemResolver\ItemResolverInterface;

/**
 * Class Configurable
 *
 * @package Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType
 */
class Configurable extends DefaultType implements ItemResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolveOrderItem($orderItem, $parentOrderItem = null)
    {
        $orderItemId = $parentOrderItem ? $parentOrderItem->getItemId() : $orderItem->getItemId();
        return [$orderItemId, $orderItem];
    }
}
