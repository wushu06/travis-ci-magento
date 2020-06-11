<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\Status as StatusModel;
use Aheadworks\Rma\Api\Data\StatusInterfaceFactory;
use Aheadworks\Rma\Api\Data\StatusSearchResultsInterface;
use Aheadworks\Rma\Api\Data\StatusSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Model\ResourceModel\Status\CollectionFactory as StatusCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StatusRepository
 *
 * @package Aheadworks\Rma\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatusRepository implements StatusRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var StatusFactory
     */
    private $statusFactory;

    /**
     * @var StatusInterfaceFactory
     */
    private $statusDataFactory;

    /**
     * @var StatusSearchResultsInterfaceFactory
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
     * @var StatusCollectionFactory
     */
    private $statusCollectionFactory;

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
     * @param StatusFactory $statusFactory
     * @param StatusInterfaceFactory $statusDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StatusSearchResultsInterfaceFactory $searchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param StatusCollectionFactory $statusCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EntityManager $entityManager,
        StatusFactory $statusFactory,
        StatusInterfaceFactory $statusDataFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StatusSearchResultsInterfaceFactory $searchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        StatusCollectionFactory $statusCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->entityManager = $entityManager;
        $this->statusFactory = $statusFactory;
        $this->statusDataFactory = $statusDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(StatusInterface $status)
    {
        /** @var \Aheadworks\Rma\Model\Status $statusModel */
        $statusModel = $this->statusFactory->create();
        if ($statusId = $status->getId()) {
            $arguments = ['store_id' => $this->storeManager->getStore()->getId()];
            $this->entityManager->load($statusModel, $statusId, $arguments);
        }
        $statusModel->setOrigData(null, $statusModel->getData());
        $this->dataObjectHelper->populateWithArray(
            $statusModel,
            $this->dataObjectProcessor->buildOutputDataArray($status, StatusInterface::class),
            StatusInterface::class
        );
        $statusModel->beforeSave();
        $this->entityManager->save($statusModel);
        $status = $this->getStatusDataObject($statusModel);
        $this->registry[$status->getId()] = $status;

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    public function get($statusId, $storeId = null)
    {
        if (!isset($this->registry[$statusId])) {
            /** @var StatusInterface $status */
            $status = $this->statusDataFactory->create();
            $storeId = $storeId ? : $this->storeManager->getStore()->getId();
            $arguments = ['store_id' => $storeId];
            $this->entityManager->load($status, $statusId, $arguments);
            if (!$status->getId()) {
                throw NoSuchEntityException::singleField('statusId', $statusId);
            }
            $this->registry[$statusId] = $status;
        }
        return $this->registry[$statusId];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $storeId = null)
    {
        /** @var StatusSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Rma\Model\ResourceModel\Status\Collection $collection */
        $collection = $this->statusCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, StatusInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType()
                    ? $filter->getConditionType()
                    : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
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

        $codes = [];
        /** @var StatusModel $statusModel */
        foreach ($collection as $statusModel) {
            $codes[] = $this->getStatusDataObject($statusModel);
        }
        $searchResults->setItems($codes);
        return $searchResults;
    }

    /**
     * Retrieves status data object using code model
     *
     * @param StatusModel $status
     * @return StatusInterface
     */
    private function getStatusDataObject(StatusModel $status)
    {
        /** @var StatusInterface $statusDataObject */
        $statusDataObject = $this->statusDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $statusDataObject,
            $this->dataObjectProcessor->buildOutputDataArray($status, StatusInterface::class),
            StatusInterface::class
        );
        return $statusDataObject;
    }
}
