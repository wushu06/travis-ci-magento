<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status\Request;

use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrderBuilderFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class StatusList
 *
 * @package Aheadworks\Rma\Model\Status\Request
 */
class StatusList
{
    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param StatusRepositoryInterface $statusRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SortOrderBuilderFactory $sortOrderBuilderFactory
     */
    public function __construct(
        StatusRepositoryInterface $statusRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SortOrderBuilderFactory $sortOrderBuilderFactory
    ) {
        $this->statusRepository = $statusRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilderFactory->create();
        $this->sortOrderBuilder = $sortOrderBuilderFactory->create();
    }

    /**
     * Get list of request statuses ordered by sort order
     *
     * @return StatusInterface[]
     * @throws LocalizedException
     */
    public function retrieve()
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(StatusInterface::SORT_ORDER)
            ->setAscendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);

        return $this->statusRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
    }

    /**
     * Get search criteria builder to set additional filters
     *
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }
}
