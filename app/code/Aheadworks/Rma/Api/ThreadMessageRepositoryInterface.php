<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Thread message CRUD interface
 * @api
 */
interface ThreadMessageRepositoryInterface
{
    /**
     * Save thread message
     *
     * @param \Aheadworks\Rma\Api\Data\ThreadMessageInterface $threadMessage
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\Rma\Api\Data\ThreadMessageInterface $threadMessage);

    /**
     * Retrieve thread message by id
     *
     * @param int $threadMessageId
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($threadMessageId);

    /**
     * Retrieve thread message matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
