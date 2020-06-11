<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option;

use Aheadworks\Rma\Api\CustomFieldOptionActionRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterfaceFactory as ActionInterfaceFactory;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface as ActionInterface;
use Aheadworks\Rma\Model\CustomField\Option\ActionFactory as ActionModelFactory;
use Aheadworks\Rma\Model\CustomField\Option\Action as ActionModel;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Aheadworks\Rma\Model\ResourceModel\CustomField\Option\Action as ActionResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionSearchResultsInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionSearchResultsInterfaceFactory;
use Aheadworks\Rma\Model\ResourceModel\CustomField\Option\Action\CollectionFactory as ActionCollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\CustomField\Option\Action\Collection as ActionCollection;

/**
 * Class ActionRepository
 *
 * @package Aheadworks\Rma\Model\CustomField\Option
 */
class ActionRepository implements CustomFieldOptionActionRepositoryInterface
{
    /**
     * @var ActionResource
     */
    private $resource;

    /**
     * @var ActionInterfaceFactory
     */
    private $actionDataFactory;

    /**
     * @var ActionModelFactory
     */
    private $actionFactory;

    /**
     * @var CustomFieldOptionActionSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var ActionCollectionFactory
     */
    private $actionCollectionFactory;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param ActionInterfaceFactory $actionDataFactory
     * @param ActionFactory $actionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param ActionResource $resource
     * @param CustomFieldOptionActionSearchResultsInterfaceFactory $searchResultsFactory
     * @param ActionCollectionFactory $actionCollectionFactory
     */
    public function __construct(
        ActionInterfaceFactory $actionDataFactory,
        ActionModelFactory $actionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        ActionResource $resource,
        CustomFieldOptionActionSearchResultsInterfaceFactory $searchResultsFactory,
        ActionCollectionFactory $actionCollectionFactory
    ) {
        $this->actionDataFactory = $actionDataFactory;
        $this->actionFactory = $actionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->resource = $resource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($actionId)
    {
        if (!isset($this->registry[$actionId])) {
            /** @var Action $action */
            $action = $this->actionDataFactory->create();
            $this->resource->load($action, $actionId);
            if (!$action->getId()) {
                throw NoSuchEntityException::singleField('action id', $actionId);
            }
            $this->registry[$actionId] = $action;
        }
        return $this->registry[$actionId];
    }

    /**
     * @inheritdoc
     */
    public function save(ActionInterface $action)
    {
        try {
            $this->resource->save($action);
            $this->registry[$action->getId()] = $action;
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var CustomFieldOptionActionSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var ActionCollection $collection */
        $collection = $this->actionCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType()
                    ? $filter->getConditionType()
                    : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $actions = [];
        /** @var ActionModel $actionModel */
        foreach ($collection as $actionModel) {
            $actions[] = $this->getActionDataObject($actionModel);
        }
        $searchResults->setItems($actions);
        return $searchResults;
    }

    /**
     * Retrieve action data object using action model
     *
     * @param ActionModel $actionModel
     * @return ActionInterface
     */
    private function getActionDataObject(ActionModel $actionModel)
    {
        /** @var ActionInterface $action */
        $action = $this->actionFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $action,
            $this->dataObjectProcessor->buildOutputDataArray($actionModel, ActionInterface::class),
            ActionInterface::class
        );
        return $action;
    }
}
