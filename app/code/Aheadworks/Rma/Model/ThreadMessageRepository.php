<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Aheadworks\Rma\Api\ThreadMessageRepositoryInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\ThreadMessage as ThreadMessageModel;
use Aheadworks\Rma\Api\Data\ThreadMessageInterfaceFactory;
use Aheadworks\Rma\Api\Data\ThreadMessageSearchResultsInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Model\ResourceModel\ThreadMessage\CollectionFactory as ThreadMessageCollectionFactory;

/**
 * Class ThreadMessageRepository
 *
 * @package Aheadworks\Rma\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThreadMessageRepository implements ThreadMessageRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ThreadMessageFactory
     */
    private $threadMessageFactory;

    /**
     * @var ThreadMessageInterfaceFactory
     */
    private $threadMessageDataFactory;

    /**
     * @var ThreadMessageSearchResultsInterfaceFactory
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
     * @var ThreadMessageCollectionFactory
     */
    private $threadMessageCollectionFactory;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param EntityManager $entityManager
     * @param ThreadMessageFactory $threadMessageFactory
     * @param ThreadMessageInterfaceFactory $threadMessageDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param ThreadMessageSearchResultsInterfaceFactory $searchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ThreadMessageCollectionFactory $threadMessageCollectionFactory
     */
    public function __construct(
        EntityManager $entityManager,
        ThreadMessageFactory $threadMessageFactory,
        ThreadMessageInterfaceFactory $threadMessageDataFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        ThreadMessageSearchResultsInterfaceFactory $searchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ThreadMessageCollectionFactory $threadMessageCollectionFactory
    ) {
        $this->entityManager = $entityManager;
        $this->threadMessageFactory = $threadMessageFactory;
        $this->threadMessageDataFactory = $threadMessageDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->threadMessageCollectionFactory = $threadMessageCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ThreadMessageInterface $threadMessage)
    {
        /** @var \Aheadworks\Rma\Model\ThreadMessage $threadMessageModel */
        $threadMessageModel = $this->threadMessageFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $threadMessageModel,
            $this->dataObjectProcessor->buildOutputDataArray($threadMessage, ThreadMessageInterface::class),
            ThreadMessageInterface::class
        );
        $threadMessageModel->beforeSave();
        $this->entityManager->save($threadMessageModel);
        $threadMessage = $this->getThreadMessageDataObject($threadMessageModel);
        $this->registry[$threadMessage->getId()] = $threadMessage;

        return $threadMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function get($threadMessageId)
    {
        if (!isset($this->registry[$threadMessageId])) {
            /** @var ThreadMessageInterface $code */
            $threadMessage = $this->threadMessageDataFactory->create();
            $this->entityManager->load($threadMessage, $threadMessageId);
            if (!$threadMessage->getId()) {
                throw NoSuchEntityException::singleField('threadMessageId', $threadMessageId);
            }
            $this->registry[$threadMessageId] = $threadMessage;
        }
        return $this->registry[$threadMessageId];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ThreadMessageSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var \Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection $collection */
        $collection = $this->threadMessageCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection, ThreadMessageInterface::class);
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

        $fields = [];
        /** @var ThreadMessageModel $threadMessageModel */
        foreach ($collection as $threadMessageModel) {
            $fields[] = $this->getThreadMessageDataObject($threadMessageModel);
        }
        $searchResults->setItems($fields);
        return $searchResults;
    }

    /**
     * Retrieves thread message data object using code model
     *
     * @param ThreadMessageModel $threadMessage
     * @return ThreadMessageInterface
     */
    private function getThreadMessageDataObject(ThreadMessageModel $threadMessage)
    {
        /** @var ThreadMessageInterface $threadMessageDataObject */
        $threadMessageDataObject = $this->threadMessageDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $threadMessageDataObject,
            $this->dataObjectProcessor->buildOutputDataArray($threadMessage, ThreadMessageInterface::class),
            ThreadMessageInterface::class
        );
        return $threadMessageDataObject;
    }
}
