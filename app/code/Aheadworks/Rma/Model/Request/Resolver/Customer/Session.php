<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Resolver\Customer;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Session
 *
 * @package Aheadworks\Rma\Model\Request\Resolver\Customer
 */
class Session
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var DataObject|null
     */
    private $customerData;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Retrieve customer id by request
     *
     * @param RequestInterface $request
     * @return int|null
     */
    public function getCustomerId($request)
    {
        return $this->getCustomerData($request)->getData('customer_id');
    }

    /**
     * Retrieve customer email by request
     *
     * @param RequestInterface $request
     * @return string|null
     */
    public function getCustomerEmail($request)
    {
        return $this->getCustomerData($request)->getData('customer_email');
    }

    /**
     * Retrieve customer full name by request
     *
     * @param RequestInterface $request
     * @return string|null
     */
    public function getCustomerFullName($request)
    {
        return $this->getCustomerData($request)->getData('customer_full_name');
    }

    /**
     * Retrieve customer data by request
     *
     * @param RequestInterface $request
     * @return DataObject
     */
    private function getCustomerData($request)
    {
        if (null !== $this->customerData) {
            return $this->customerData;
        }

        $this->customerData = $this->dataObjectFactory->create();
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);

            $customerEmail = $customer->getEmail();
            $customerFullName = $customer->getFirstname() . ' ' . $customer->getLastname();
        } else {
            $order = $this->orderRepository->get($request->getOrderId());
            $billingAddress = $order->getBillingAddress();

            $customerId = null;
            $customerEmail = $order->getCustomerEmail();
            $customerFullName = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        }

        $this->customerData
            ->setData('customer_id', $customerId)
            ->setData('customer_email', $customerEmail)
            ->setData('customer_full_name', $customerFullName);

        return $this->customerData;
    }
}
