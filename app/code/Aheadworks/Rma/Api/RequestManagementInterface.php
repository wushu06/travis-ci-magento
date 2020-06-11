<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Request management interface
 * @api
 */
interface RequestManagementInterface
{
    /**
     * Create new request
     *
     * @param \Aheadworks\Rma\Api\Data\RequestInterface $request
     * @param bool $causedByAdmin
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createRequest(\Aheadworks\Rma\Api\Data\RequestInterface $request, $causedByAdmin, $storeId = null);

    /**
     * Update request
     *
     * @param \Aheadworks\Rma\Api\Data\RequestInterface $request
     * @param bool $causedByAdmin
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateRequest(\Aheadworks\Rma\Api\Data\RequestInterface $request, $causedByAdmin, $storeId = null);

    /**
     * Retrieve print label url
     *
     * @param int $requestId
     * @param int|null $storeId
     * @return string
     */
    public function getPrintLabelUrl($requestId, $storeId = null);

    /**
     * Retrieve print label url for admin
     *
     * @param int $requestId
     * @return string
     */
    public function getPrintLabelUrlForAdmin($requestId);

    /**
     * Change status
     *
     * @param int $requestId
     * @param int $status
     * @param bool $causedByAdmin
     * @param int|null $storeId
     * @return bool
     */
    public function changeStatus($requestId, $status, $causedByAdmin, $storeId = null);
}
