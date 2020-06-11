<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Resolver;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Customer
 *
 * @package Aheadworks\Rma\Model\Request\Resolver
 */
class Customer
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var Order
     */
    private $orderResolver;

    /**
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupRepositoryInterface $customerGroupRepository
     * @param TimezoneInterface $localeDate
     * @param Order $orderResolver
     */
    public function __construct(
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        GroupRepositoryInterface $customerGroupRepository,
        TimezoneInterface $localeDate,
        Order $orderResolver
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->localeDate = $localeDate;
        $this->orderResolver = $orderResolver;
    }

    /**
     * Retrieve customer name
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getName($request)
    {
        if ($request->getCustomerId() && ($customer = $this->getCustomerById($request->getCustomerId()))) {
            return $customer->getFirstname() . ' ' . $customer->getLastname();
        }

        return $request->getCustomerName();
    }

    /**
     * Retrieve customer email
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getEmail($request)
    {
        if ($request->getCustomerId() && ($customer = $this->getCustomerById($request->getCustomerId()))) {
            return $customer->getEmail();
        }

        return $request->getCustomerEmail();
    }

    /**
     * Retrieve customer group
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getGroup($request)
    {
        $groupId = Group::NOT_LOGGED_IN_ID;
        if ($request->getCustomerId() && ($customer = $this->getCustomerById($request->getCustomerId()))) {
            $groupId = $customer->getGroupId();
        }

        return $this->getGroupNameById($groupId);
    }

    /**
     * Retrieve date of customer created at account
     *
     * @param RequestInterface $request
     * @param int|null $storeId
     * @param int $format
     * @return string
     */
    public function getCreatedAt($request, $storeId = null, $format = \IntlDateFormatter::SHORT)
    {
        if ($request->getCustomerId() && ($customer = $this->getCustomerById($request->getCustomerId()))) {
            $date = $customer->getCreatedAt();
            $date = $this->localeDate->formatDate(
                $this->localeDate->scopeDate($storeId, $date, true),
                $format,
                false
            );

            return $date;
        }

        return '';
    }

    /**
     * Retrieve customer id
     *
     * @param RequestInterface $request
     * @return int|null
     */
    public function getCustomerId($request)
    {
        if ($request->getCustomerId() && ($customer = $this->getCustomerById($request->getCustomerId()))) {
            return $customer->getId();
        }

        return null;
    }

    /**
     * Retrieve address
     *
     * @param RequestInterface $request
     * @return OrderAddressInterface|AddressInterface|null
     */
    public function getAddress($request)
    {
        $orderAddress = $this->orderResolver->getAddress($request->getOrderId());
        if (!empty($orderAddress)) {
            return $orderAddress;
        }

        if ($request->getCustomerId() && ($customer = $this->getCustomerById($request->getCustomerId()))) {
            return $this->getDefaultCustomerAddress($customer->getId());
        }

        return null;
    }

    /**
     * Retrieve default customer address
     *
     * @param $customerId
     * @return AddressInterface
     */
    private function getDefaultCustomerAddress($customerId)
    {
        return $this->accountManagement->getDefaultShippingAddress($customerId)
            ? $this->accountManagement->getDefaultShippingAddress($customerId)
            : $this->accountManagement->getDefaultBillingAddress($customerId);
    }

    /**
     * Retrieve customer group name
     *
     * @param int $groupId
     * @return string
     */
    private function getGroupNameById($groupId)
    {
        try {
            $group = $this->customerGroupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            return '';
        }

        return $group->getCode();
    }

    /**
     * Retrieve customer by id
     *
     * @param int $customerId
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerById($customerId)
    {
        try {
            return $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
