<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Customer request management interface
 * @api
 */
interface CustomerRequestManagementInterface
{
    /**
     * Create new request
     *
     * @param \Aheadworks\Rma\Api\Data\RequestInterface $request
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createRequest(\Aheadworks\Rma\Api\Data\RequestInterface $request, $storeId = null);

    /**
     * Update request
     *
     * @param \Aheadworks\Rma\Api\Data\RequestInterface $request
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateRequest(\Aheadworks\Rma\Api\Data\RequestInterface $request, $storeId = null);

    /**
     * Retrieve print label by request external link
     *
     * @param string $externalLink
     * @param int|null $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRequestPrintLabelUrl($externalLink, $storeId = null);
}
