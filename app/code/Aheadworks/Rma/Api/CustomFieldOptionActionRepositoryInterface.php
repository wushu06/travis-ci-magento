<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Interface CustomFieldOptionActionRepository
 * @api
 */
interface CustomFieldOptionActionRepositoryInterface
{
    /**
     * Retrieve custom field option action
     *
     * @param int $actionId
     * @return \Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($actionId);

    /**
     * Save custom field option action
     *
     * @param \Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface $action
     * @return \Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface $action);

    /**
     * Retrieve custom field option action matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Rma\Api\Data\CustomFieldOptionActionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
