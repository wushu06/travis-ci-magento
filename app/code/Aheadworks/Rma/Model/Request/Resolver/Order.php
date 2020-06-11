<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Order
 *
 * @package Aheadworks\Rma\Model\Request\Resolver
 */
class Order
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        TimezoneInterface $localeDate
    ) {
        $this->orderRepository = $orderRepository;
        $this->localeDate = $localeDate;
    }

    /**
     * Retrieve order increment id
     *
     * @param int $orderId
     * @return string
     */
    public function getIncrementId($orderId)
    {
        $order = $this->getOrderById($orderId);
        if ($order) {
            return $order->getIncrementId();
        }

        return $orderId;
    }

    /**
     * Retrieve order created at
     *
     * @param int $orderId
     * @param int|null $storeId
     * @param int $format
     * @return string
     */
    public function getCreatedAt($orderId, $storeId = null, $format = \IntlDateFormatter::SHORT)
    {
        $order = $this->getOrderById($orderId);
        if ($order) {
            $date = $this->getOrderById($orderId)->getCreatedAt();
            return $this->localeDate->formatDate(
                $this->localeDate->scopeDate($storeId, $date, true),
                $format,
                false
            );
        }

        return '';
    }

    /**
     * Retrieve order address
     *
     * @param int $orderId
     * @return OrderAddressInterface|null
     */
    public function getAddress($orderId)
    {
        $order = $this->getOrderById($orderId);
        if ($order) {
            return $order->getShippingAddress() ? $order->getShippingAddress() :  $order->getBillingAddress();
        }

        return null;
    }

    /**
     * Retrieve order by id
     *
     * @param int $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface|bool
     */
    private function getOrderById($orderId)
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
