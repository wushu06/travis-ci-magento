<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Sales\Api\Data\CreditmemoInterface as SalesCreditmemoInterface;

/**
 * Interface CreditmemoInterface
 * @api
 */
interface CreditmemoInterface extends SalesCreditmemoInterface
{
    /**
     * AW RMA request ID used for mapping request to creditmemo
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
