<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\Request as RequestModel;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestSearchResultsInterface;
use Aheadworks\Rma\Api\Data\RequestSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory as RequestCollectionFactory;

/**
 * Class RequestRepository
 *
 * @package Aheadworks\Rma\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestRepository implements RequestRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var RequestInterfaceFactory
     */
    private $requestDataFactory;

    /**
     * @var RequestSearchResultsInterfaceFactory
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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RequestCollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @var array
     */
    private $registryByExternalLink = [];

    /**
     * @param EntityManager $entityManager
     * @param RequestFactory $requestFactory
     * @param RequestInterfaceFactory $requestDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param RequestSearchResultsInterfaceFactory $searchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestCollectionFactory $requestCollectionFactory
     */
    public function __construct(
        EntityManager $entityManager,
        RequestFactory $requestFactory,
        RequestInterfaceFactory $requestDataFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        RequestSearchResultsInterfaceFactory $searchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestCollectionFactory $requestCollectionFactory
    ) {
        $this->entityManager = $entityManager;
        $this->requestFactory = $requestFactory;
        $this->requestDataFactory = $requestDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->requestCollectionFactory = $requestCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestInterface $request)
    {
        /** @var \Aheadworks\Rma\Model\Request $requestModel */
        $requestModel = $this->requestFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $requestModel,
            $this->dataObjectProcessor->buildOutputDataArray($request, RequestInterface::class),
            RequestInterface::class
        );
        $requestModel->beforeSave();
        $this->entityManager->save($requestModel);
        $request = $this->getRequestDataObject($requestModel);
        $this->registry[$request->getId()] = $request;
        $this->registryByExternalLink[$request->getExternalLink()] = $request;

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function get($requestId, $noCache = false)
    {
        if (!isset($this->registry[$requestId]) || $noCache) {
            /** @var RequestInterface $code */
            $request = $this->requestDataFactory->create();
            $this->entityManager->load($request, $requestId);
            if (!$request->getId()) {
                throw NoSuchEntityException::singleField('requestId', $requestId);
            }
            $this->registry[$requestId] = $request;
            $this->registryByExternalLink[$request->getExternalLink()] = $request;
        }
        return $this->registry[$requestId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByExternalLink($externalLink)
    {
        if (!isset($this->registryByExternalLink[$externalLink])) {
            $this->searchCriteriaBuilder->addFilter(RequestInterface::EXTERNAL_LINK, $externalLink);
            $requests = $this->getList($this->searchCriteriaBuilder->create())->getItems();
            $request = array_shift($requests);
            if (empty($request)) {
                throw NoSuchEntityException::singleField('requestExternalLink', $externalLink);
            }

            $this->registry[$request->getId()] = $request;
            $this->registryByExternalLink[$request->getExternalLink()] = $request;
        }
        return $this->registryByExternalLink[$externalLink];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var RequestSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Rma\Model\ResourceModel\Request\Collection $collection */
        $collection = $this->requestCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, RequestInterface::class);
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

        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $requests = [];
        /** @var RequestModel $requestModel */
        foreach ($collection as $requestModel) {
            $requests[] = $this->getRequestDataObject($requestModel);
        }
        $searchResults->setItems($requests);
        return $searchResults;
    }

    /**
     * Retrieves request data object using code model
     *
     * @param RequestModel $request
     * @return RequestInterface
     */
    private function getRequestDataObject(RequestModel $request)
    {
        $requestData = $this->dataObjectProcessor->buildOutputDataArray($request, RequestInterface::class);
        if (!is_object($request->getPrintLabel())) {
            $requestData[RequestInterface::PRINT_LABEL] = $request->getPrintLabel();
        }

        /** @var RequestInterface $requestDataObject */
        $requestDataObject = $this->requestDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $requestDataObject,
            $requestData,
            RequestInterface::class
        );
        return $requestDataObject;
    }
}
