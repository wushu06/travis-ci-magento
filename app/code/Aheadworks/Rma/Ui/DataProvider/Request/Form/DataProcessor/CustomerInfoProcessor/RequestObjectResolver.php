<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\CustomerInfoProcessor;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory as RmaRequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestInterface as RmaRequestInterface;

/**
 * Class RequestObjectResolver
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\CustomerInfoProcessor
 */
class RequestObjectResolver
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RmaRequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestRepositoryInterface $requestRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param RmaRequestInterfaceFactory $requestFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RequestRepositoryInterface $requestRepository,
        DataObjectHelper $dataObjectHelper,
        RmaRequestInterfaceFactory $requestFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->requestRepository = $requestRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Resolve request by data
     *
     * @param array $data
     * @return RmaRequestInterface|null
     */
    public function resolve($data)
    {
        $requestId = isset($data['id']) ? $data['id'] : null;
        if ($requestId) {
            $request = $this->getRequest($requestId);
        } else {
            $orderId = isset($data['order_id']) ? $data['order_id'] : null;
            $requestData = $this->extractRequestDataFromOrder($orderId);
            $request = $this->createRequestObject($requestData);
        }

        return $request;
    }

    /**
     * @param $requestId
     * @return RmaRequestInterface|null
     */
    private function getRequest($requestId)
    {
        try {
            return $this->requestRepository->get($requestId);
        } catch (NoSuchEntityException $e) {
        }

        return null;
    }

    /**
     * Create request object
     *
     * @param array $requestData
     * @return RmaRequestInterface|null
     */
    private function createRequestObject($requestData)
    {
        if (empty($requestData)) {
            return null;
        }
        $request = $this->requestFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $request,
            $requestData,
            RmaRequestInterface::class
        );

        return $request;
    }

    /**
     * Extract request data from order
     *
     * @param int $orderId
     * @return array
     */
    private function extractRequestDataFromOrder($orderId)
    {
        $requestData = [];
        try {
            $order = $this->orderRepository->get($orderId);
            $requestData = array_merge(
                $this->extractCustomerData($order),
                [RmaRequestInterface::ORDER_ID => $order->getEntityId()]
            );
        } catch (NoSuchEntityException $e) {
        }

        return $requestData;
    }

    /**
     * Extract customer data
     *
     * @param OrderInterface $order
     * @return array
     */
    private function extractCustomerData($order)
    {
        if ($order->getCustomerId()) {
            $customerData = [
                RmaRequestInterface::CUSTOMER_ID => $order->getCustomerId(),
                RmaRequestInterface::CUSTOMER_NAME =>
                    $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                RmaRequestInterface::CUSTOMER_EMAIL => $order->getCustomerEmail(),
            ];
        } else {
            $billingAddress = $order->getBillingAddress();
            $customerData = [
                RmaRequestInterface::CUSTOMER_ID => null,
                RmaRequestInterface::CUSTOMER_EMAIL => $order->getCustomerEmail(),
                RmaRequestInterface::CUSTOMER_NAME =>
                    $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname()
            ];
        }
        return $customerData;
    }
}
