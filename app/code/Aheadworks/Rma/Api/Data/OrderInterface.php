<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;

/**
 * Interface OrderInterface
 * @api
 */
interface OrderInterface extends SalesOrderInterface
{
    /**
     * AW RMA request ID used for mapping request to order
     */
    const AW_RMA_REQUEST_ID = 'aw_rma_request_id';

    /**
     * Get AW RMA request ID
     *
     * @return int|null
     */
    public function getAwRmaRequestId();

    /**
     * Set AW RMA request ID
     *
     * @param int|null $requestId
     * @return $this
     */
    public function setAwRmaRequestId($requestId);
}
