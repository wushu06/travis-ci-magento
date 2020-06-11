<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Custom field CRUD interface
 * @api
 */
interface CustomFieldRepositoryInterface
{
    /**
     * Save custom field
     *
     * @param \Aheadworks\Rma\Api\Data\CustomFieldInterface $customField
     * @return \Aheadworks\Rma\Api\Data\CustomFieldInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Rma\Api\Data\CustomFieldInterface $customField);

    /**
     * Retrieve custom field by id
     *
     * @param int $customFieldId
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\CustomFieldInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customFieldId, $storeId = null);

    /**
     * Retrieve custom field matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\CustomFieldSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria, $storeId = null);
}
