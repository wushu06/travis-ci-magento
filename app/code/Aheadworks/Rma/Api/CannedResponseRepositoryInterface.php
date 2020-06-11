<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Canned response CRUD interface
 * @api
 */
interface CannedResponseRepositoryInterface
{
    /**
     * Save canned response
     *
     * @param \Aheadworks\Rma\Api\Data\CannedResponseInterface $cannedResponse
     * @return \Aheadworks\Rma\Api\Data\CannedResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Rma\Api\Data\CannedResponseInterface $cannedResponse);

    /**
     * Retrieve canned response by id
     *
     * @param int $cannedResponseId
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\CannedResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($cannedResponseId, $storeId = null);

    /**
     * Retrieve canned response matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\CannedResponseSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $storeId = null);

    /**
     * Delete canned response
     *
     * @param \Aheadworks\Rma\Api\Data\CannedResponseInterface $cannedResponse
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Aheadworks\Rma\Api\Data\CannedResponseInterface $cannedResponse);

    /**
     * Delete canned response by ID
     *
     * @param int $cannedResponseId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($cannedResponseId);
}
