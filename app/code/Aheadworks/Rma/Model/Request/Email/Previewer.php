<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Email;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionInterface;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Model\Email\EmailMetadataInterface;
use Aheadworks\Rma\Model\Request\Email\Processor\AdminChangedStatus;
use Aheadworks\Rma\Model\Request\Email\Processor\CustomerChangedStatus;
use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Aheadworks\Rma\Model\Source\CustomField\Type;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Previewer
 *
 * @package Aheadworks\Rma\Model\Request\Email
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Previewer
{
    /**
     * @var ProcessorList
     */
    private $processorList;

    /**
     * @var RequestInterfaceFactory
     */
    private $rmaRequestFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param ProcessorList $processorList
     * @param RequestInterfaceFactory $rmaRequestFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ProcessorList $processorList,
        RequestInterfaceFactory $rmaRequestFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        CustomFieldRepositoryInterface $customFieldRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->processorList = $processorList;
        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->customFieldRepository = $customFieldRepository;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Retrieve preview
     *
     * @param int $storeId
     * @param StatusInterface $status
     * @param bool $toAdmin
     * @return EmailMetadataInterface
     */
    public function preview($storeId, $status, $toAdmin)
    {
        $requestObject = $this->rmaRequestFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $requestObject,
            $this->getRequestSampleData($storeId),
            RequestInterface::class
        );
        $processorType = $toAdmin
            ? ProcessorList::ADMIN_CHANGED_STATUS_PROCESSOR
            : ProcessorList::CUSTOMER_CHANGED_STATUS_PROCESSOR;
        /** @var AdminChangedStatus|CustomerChangedStatus $processor */
        $processor = $this->processorList->getProcessor($processorType);
        $emailMetadata = $processor
            ->setRequest($requestObject)
            ->setStatus($status)
            ->setStoreId($storeId)
            ->process();

        return $emailMetadata;
    }

    /**
     * Retrieve request sample data
     *
     * @param int $storeId
     * @return array
     */
    private function getRequestSampleData($storeId)
    {
        $order = $this->getRandomOrder();
        $orderId = $order ? $order->getEntityId() : null;
        $currentDate = new \DateTime();
        $request = [
            RequestInterface::ID => 1,
            RequestInterface::INCREMENT_ID => '000000001',
            RequestInterface::CREATED_AT => $currentDate->format(DateTime::DATETIME_PHP_FORMAT),
            RequestInterface::ORDER_ID => $orderId,
            RequestInterface::STORE_ID => $storeId,
            RequestInterface::CUSTOMER_NAME => 'John Doe',
            RequestInterface::CUSTOMER_EMAIL => 'john_doe@example.com',
            RequestInterface::CUSTOM_FIELDS => $this->getCustomFields(Refers::REQUEST),
            RequestInterface::ORDER_ITEMS => $this->getRequestOrderItems($orderId),
            RequestInterface::THREAD_MESSAGE => [
                ThreadMessageInterface::TEXT => 'Dummy message'
            ]
        ];

        return $request;
    }

    /**
     * Retrieve random order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    private function getRandomOrder()
    {
        $this->searchCriteriaBuilder
            ->setCurrentPage(1)
            ->setPageSize(1);
        $orders = $this->orderRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $order = null;
        if ($orders) {
            $order = array_shift($orders);
        }

        return $order;
    }

    /**
     * Retrieve request order items
     *
     * @param $orderId
     * @return array
     */
    private function getRequestOrderItems($orderId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(OrderItemInterface::ORDER_ID, $orderId)
            ->setCurrentPage(1)
            ->setPageSize(2);

        $requestOrderItems = [];
        $orderItems = $this->orderItemRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        foreach ($orderItems as $orderItem) {
            $requestOrderItems[] = [
                RequestItemInterface::ITEM_ID => $orderItem->getItemId(),
                RequestItemInterface::QTY => 1,
                RequestItemInterface::CUSTOM_FIELDS => $this->getCustomFields(Refers::ITEM)
            ];
        }

        return $requestOrderItems;
    }

    /**
     * Retrieve custom fields
     *
     * @param int $refersTo
     * @return array
     */
    private function getCustomFields($refersTo)
    {
        $this->searchCriteriaBuilder
            ->addFilter(CustomFieldInterface::REFERS, $refersTo);

        $requestCustomFields = [];
        $customFields = $this->customFieldRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        foreach ($customFields as $customField) {
            $value = $this->getCustomFieldValue($customField);
            if (!$value) {
                continue;
            }
            $requestCustomFields[] = [
                RequestCustomFieldValueInterface::FIELD_ID => $customField->getId(),
                RequestCustomFieldValueInterface::VALUE => $value
            ];
        }

        return $requestCustomFields;
    }

    /**
     * Retrieve custom field value
     *
     * @param CustomFieldInterface $customField
     * @return string|null
     */
    private function getCustomFieldValue($customField)
    {
        $value = null;
        switch ($customField->getType()) {
            case Type::MULTI_SELECT:
            case Type::SELECT:
                /** @var CustomFieldOptionInterface $option */
                $option = $customField->getOptions() ? $customField->getOptions()[0] : null;
                $value = $option ? $option->getId() : null;
                break;
            case Type::TEXT:
            case Type::TEXT_AREA:
                $value = 'sample';
                break;
        }

        return $value;
    }
}
