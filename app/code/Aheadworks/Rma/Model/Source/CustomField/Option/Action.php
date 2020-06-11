<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\CustomField\Option;

use Magento\Framework\Option\ArrayInterface;
use Aheadworks\Rma\Api\CustomFieldOptionActionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Action
 *
 * @package Aheadworks\Rma\Model\Source\CustomField\Option
 */
class Action implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var CustomFieldOptionActionRepositoryInterface
     */
    private $actionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CustomFieldOptionActionRepositoryInterface $actionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CustomFieldOptionActionRepositoryInterface $actionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->actionRepository = $actionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get actions as options
     *
     * @return array
     * @throws LocalizedException
     */
    public function getActionsAsOptions()
    {
        $actions = $this->getActions();
        $options = [];
        foreach ($actions as $action) {
            $options[] = [
                'value' => $action->getId(),
                'label' => $action->getTitle()
            ];
        }

        return $options;
    }

    /**
     * Get list of actions
     *
     * @return CustomFieldOptionActionInterface[]
     * @throws LocalizedException
     */
    public function getActions()
    {
        return $this->actionRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options[] = [
                'value' => 0,
                'label' => __('Select...')
            ];
            $this->options = array_merge($this->options, $this->getActionsAsOptions());
        }
        return $this->options;
    }
}
