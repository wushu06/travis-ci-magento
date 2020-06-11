<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\Storage;

use Aheadworks\Rma\Api\Data\OrderInterface;

/**
 * Class CurrentOrder
 *
 * @package Aheadworks\Rma\Model\Request\Order\Storage
 */
class CurrentOrder
{
    /**
     * @var OrderInterface|null
     */
    private $currentOrder = null;

    /**
     * Set current order
     *
     * @param OrderInterface $order
     */
    public function setOrder($order)
    {
        $this->currentOrder = $order;
    }

    /**
     * Get current order
     *
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        return $this->currentOrder;
    }
}
