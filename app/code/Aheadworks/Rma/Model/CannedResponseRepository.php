<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\CannedResponseRepositoryInterface;
use Aheadworks\Rma\Api\Data\CannedResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\CannedResponse as CannedResponseModel;
use Aheadworks\Rma\Api\Data\CannedResponseInterfaceFactory;
use Aheadworks\Rma\Api\Data\CannedResponseSearchResultsInterface;
use Aheadworks\Rma\Api\Data\CannedResponseSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\Store;
use Aheadworks\Rma\Model\ResourceModel\CannedResponse\CollectionFactory as CannedResponseCollectionFactory;

/**
 * Class CannedResponseRepository
 *
 * @package Aheadworks\Rma\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CannedResponseRepository implements CannedResponseRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CannedResponseFactory
     */
    private $cannedResponseFactory;

    /**
     * @var CannedResponseInterfaceFactory
     */
    private $cannedResponseDataFactory;

    /**
     * @var CannedResponseSearchResultsInterfaceFactory
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
     * @var CannedResponseCollectionFactory
     */
    private $cannedResponseCollectionFactory;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param EntityManager $entityManager
     * @param CannedResponseFactory $cannedResponseFactory
     * @param CannedResponseInterfaceFactory $cannedResponseDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CannedResponseSearchResultsInterfaceFactory $searchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CannedResponseCollectionFactory $cannedResponseCollectionFactory
     */
    public function __construct(
        EntityManager $entityManager,
        CannedResponseFactory $cannedResponseFactory,
        CannedResponseInterfaceFactory $cannedResponseDataFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        CannedResponseSearchResultsInterfaceFactory $searchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CannedResponseCollectionFactory $cannedResponseCollectionFactory
    ) {
        $this->entityManager = $entityManager;
        $this->cannedResponseFactory = $cannedResponseFactory;
        $this->cannedResponseDataFactory =$cannedResponseDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->cannedResponseCollectionFactory = $cannedResponseCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CannedResponseInterface $cannedResponse)
    {
        /** @var \Aheadworks\Rma\Model\CannedResponse $cannedResponseModel */
        $cannedResponseModel = $this->cannedResponseFactory->create();
        if ($cannedResponseId = $cannedResponse->getId()) {
            $arguments = ['store_id' => Store::DEFAULT_STORE_ID];
            $this->entityManager->load($cannedResponseModel, $cannedResponseId, $arguments);
        }
        $cannedResponseModel->setOrigData(null, $cannedResponseModel->getData());
        $this->dataObjectHelper->populateWithArray(
            $cannedResponseModel,
            $this->dataObjectProcessor->buildOutputDataArray($cannedResponse, CannedResponseInterface::class),
            CannedResponseInterface::class
        );
        $cannedResponseModel->validateBeforeSave();
        $this->entityManager->save($cannedResponseModel);
        $cannedResponse = $this->getCannedResponseDataObject($cannedResponseModel);
        $this->registry[$cannedResponse->getId()] = $cannedResponse;

        return $cannedResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cannedResponseId, $storeId = null)
    {
        if (!isset($this->registry[$cannedResponseId])) {
            /** @var CannedResponseInterface $code */
            $cannedResponse = $this->cannedResponseDataFactory->create();
            $storeId = $storeId ? : Store::DEFAULT_STORE_ID;
            $arguments = ['store_id' => $storeId];
            $this->entityManager->load($cannedResponse, $cannedResponseId, $arguments);
            if (!$cannedResponse->getId()) {
                throw NoSuchEntityException::singleField('cannedResponseId', $cannedResponseId);
            }
            $this->registry[$cannedResponseId] = $cannedResponse;
        }
        return $this->registry[$cannedResponseId];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $storeId = null)
    {
        /** @var CannedResponseSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Rma\Model\ResourceModel\CannedResponse\Collection $collection */
        $collection = $this->cannedResponseCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, CannedResponseInterface::class);
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

        $storeId = $storeId ? : Store::DEFAULT_STORE_ID;
        $collection
            ->setStoreId($storeId)
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $cannedResponses = [];
        /** @var CannedResponseModel $cannedResponseModel */
        foreach ($collection as $cannedResponseModel) {
            $cannedResponses[] = $this->getCannedResponseDataObject($cannedResponseModel);
        }
        $searchResults->setItems($cannedResponses);
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CannedResponseInterface $cannedResponse)
    {
        return $this->deleteById($cannedResponse->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($cannedResponseId)
    {
        $cannedResponse = $this->get($cannedResponseId);
        $this->entityManager->delete($cannedResponse);
        if (isset($this->registry[$cannedResponseId])) {
            unset($this->registry[$cannedResponseId]);
        }
        return true;
    }

    /**
     * Retrieves canned response data object using code model
     *
     * @param CannedResponseModel $cannedResponse
     * @return CannedResponseInterface
     */
    private function getCannedResponseDataObject(CannedResponse $cannedResponse)
    {
        /** @var CannedResponseInterface $cannedResponseDataObject */
        $cannedResponseDataObject = $this->cannedResponseDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $cannedResponseDataObject,
            $this->dataObjectProcessor->buildOutputDataArray($cannedResponse, CannedResponseInterface::class),
            CannedResponseInterface::class
        );
        return $cannedResponseDataObject;
    }
}
