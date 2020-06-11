<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Request CRUD interface
 * @api
 */
interface RequestRepositoryInterface
{
    /**
     * Save request
     *
     * @param \Aheadworks\Rma\Api\Data\RequestInterface $request
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Rma\Api\Data\RequestInterface $request);

    /**
     * Retrieve request by id
     *
     * @param int $requestId
     * @param bool $noCache
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($requestId, $noCache = false);

    /**
     * Retrieve request external link
     *
     * @param string $externalLink
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByExternalLink($externalLink);

    /**
     * Retrieve request matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Rma\Api\Data\RequestSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
