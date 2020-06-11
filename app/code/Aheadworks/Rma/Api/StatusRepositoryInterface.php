<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Status CRUD interface
 * @api
 */
interface StatusRepositoryInterface
{
    /**
     * Save status
     *
     * @param \Aheadworks\Rma\Api\Data\StatusInterface $status
     * @return \Aheadworks\Rma\Api\Data\StatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Rma\Api\Data\StatusInterface $status);

    /**
     * Retrieve status by id
     *
     * @param int $statusId
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\StatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($statusId, $storeId = null);

    /**
     * Retrieve status matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\StatusSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $storeId = null);
}
