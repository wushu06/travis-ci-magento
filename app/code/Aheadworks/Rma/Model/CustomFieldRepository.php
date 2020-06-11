<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\CustomField as CustomFieldModel;
use Aheadworks\Rma\Api\Data\CustomFieldInterfaceFactory;
use Aheadworks\Rma\Api\Data\CustomFieldSearchResultsInterface;
use Aheadworks\Rma\Api\Data\CustomFieldSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory as CustomFieldCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CustomFieldRepository
 *
 * @package Aheadworks\Rma\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomFieldRepository implements CustomFieldRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomFieldFactory
     */
    private $customFieldFactory;

    /**
     * @var CustomFieldInterfaceFactory
     */
    private $customFieldDataFactory;

    /**
     * @var CustomFieldSearchResultsInterfaceFactory
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
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CustomFieldCollectionFactory
     */
    private $customFieldCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param EntityManager $entityManager
     * @param CustomFieldFactory $customFieldFactory
     * @param CustomFieldInterfaceFactory $customFieldDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CustomFieldSearchResultsInterfaceFactory $searchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CustomFieldCollectionFactory $customFieldCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EntityManager $entityManager,
        CustomFieldFactory $customFieldFactory,
        CustomFieldInterfaceFactory $customFieldDataFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        CustomFieldSearchResultsInterfaceFactory $searchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CustomFieldCollectionFactory $customFieldCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->entityManager = $entityManager;
        $this->customFieldFactory = $customFieldFactory;
        $this->customFieldDataFactory = $customFieldDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->customFieldCollectionFactory = $customFieldCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CustomFieldInterface $customField)
    {
        /** @var \Aheadworks\Rma\Model\CustomField $customFieldModel */
        $customFieldModel = $this->customFieldFactory->create();
        if ($customFieldId = $customField->getId()) {
            $arguments = ['store_id' => $this->storeManager->getStore()->getId()];
            $this->entityManager->load($customFieldModel, $customFieldId, $arguments);
        }
        $customFieldModel->setOrigData(null, $customFieldModel->getData());
        $this->dataObjectHelper->populateWithArray(
            $customFieldModel,
            $this->dataObjectProcessor->buildOutputDataArray($customField, CustomFieldInterface::class),
            CustomFieldInterface::class
        );
        $customFieldModel->beforeSave();
        $this->entityManager->save($customFieldModel);
        $customField = $this->getCustomFieldDataObject($customFieldModel);
        $this->registry[$customField->getId()] = $customField;

        return $customField;
    }

    /**
     * @inheritdoc
     */
    public function get($customFieldId, $storeId = null)
    {
        $storeId = isset($storeId) ? $storeId: $this->storeManager->getStore()->getId();
        if (!isset($this->registry[$customFieldId][$storeId])) {
            /** @var CustomFieldInterface $code */
            $customField = $this->customFieldDataFactory->create();
            $arguments = ['store_id' => $storeId];
            $this->entityManager->load($customField, $customFieldId, $arguments);
            if (!$customField->getId()) {
                throw NoSuchEntityException::singleField('customFieldId', $customFieldId);
            }
            $this->registry[$customFieldId][$storeId] = $customField;
        }
        return $this->registry[$customFieldId][$storeId];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $storeId = null)
    {
        /** @var CustomFieldSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Rma\Model\ResourceModel\CustomField\Collection $collection */
        $collection = $this->customFieldCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, CustomFieldInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == CustomFieldInterface::OPTIONS && $filter->getValue() == 'enabled') {
                    $collection->addEnabledOptionsFilter();
                } elseif ($filter->getField() == 'editable_or_visible_for_status') {
                    $collection->addEditableOrVisibleForStatusFilter($filter->getValue());
                } else {
                    $condition = $filter->getConditionType()
                        ? $filter->getConditionType()
                        : 'eq';
                    $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
                }
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        $storeId = $storeId ? : $this->storeManager->getStore()->getId();
        $collection
            ->setStoreId($storeId)
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $fields = [];
        /** @var CustomFieldModel $customFieldModel */
        foreach ($collection as $customFieldModel) {
            $fields[] = $this->getCustomFieldDataObject($customFieldModel);
        }
        $searchResults->setItems($fields);
        return $searchResults;
    }

    /**
     * Retrieves custom field data object using code model
     *
     * @param CustomFieldModel $customField
     * @return CustomFieldInterface
     */
    private function getCustomFieldDataObject(CustomFieldModel $customField)
    {
        /** @var CustomFieldInterface $customFieldDataObject */
        $customFieldDataObject = $this->customFieldDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customFieldDataObject,
            $this->dataObjectProcessor->buildOutputDataArray($customField, CustomFieldInterface::class),
            CustomFieldInterface::class
        );
        return $customFieldDataObject;
    }
}
