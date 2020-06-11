<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request;

use Aheadworks\Rma\Model\Config;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;

/**
 * Class Order
 *
 * @package Aheadworks\Rma\Model\Request
 */
class Order
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var array
     */
    private $orders = [];

    /**
     * @var array
     */
    private $isAllowedForOrder = [];

    /**
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        Config $config,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Retrieve orders by customer id
     *
     * @param int $customerId
     * @param int|null $storeId
     * @return array|\Magento\Sales\Model\Order[]
     */
    public function getOrders($customerId, $storeId = null)
    {
        if (null == $this->orders) {
            $sortOrder = $this->sortOrderBuilder
                ->setField(OrderInterface::CREATED_AT)
                ->setDirection(SortOrder::SORT_DESC)
                ->create();
            $this->searchCriteriaBuilder
                ->addFilter(OrderInterface::CUSTOMER_ID, $customerId)
                ->addSortOrder($sortOrder);

            $returnPeriod = $this->config->getReturnPeriod($storeId);
            if ($returnPeriod > 0) {
                $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
                $filterDate = $currentDate->sub(new \DateInterval('P' . $returnPeriod . 'D'));

                $this->searchCriteriaBuilder
                    ->addFilter(OrderInterface::UPDATED_AT, $filterDate->format(DateTime::DATETIME_PHP_FORMAT), 'gt');
            }

            $this->orders = $this->orderRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        }
        return $this->orders;
    }

    /**
     * Check whether the given order is allowed for RMA
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int|null $storeId
     * @return bool
     */
    public function isAllowedForOrder($order, $storeId = null)
    {
        if (!isset($this->isAllowedForOrder[$order->getId()])) {
            if ($order->getState() == 'complete') {
                $returnPeriod = $this->config->getReturnPeriod($storeId);
                if (!$returnPeriod) {
                    $this->isAllowedForOrder[$order->getId()] = true;
                    return $this->isAllowedForOrder[$order->getId()];
                }

                $lastInvoiceTime = $this->getLastInvoiceTime($order);
                if ($lastInvoiceTime
                    && $lastInvoiceTime >= strtotime(sprintf('-%d day', $returnPeriod), time())
                ) {
                    $this->isAllowedForOrder[$order->getId()] = true;
                    return $this->isAllowedForOrder[$order->getId()];
                }
            }
            $this->isAllowedForOrder[$order->getId()] = false;
        }

        return $this->isAllowedForOrder[$order->getId()];
    }

    /**
     * Retrieve last invoice time
     *
     * @param \Magento\Sales\Model\Order $order
     * @return int
     */
    private function getLastInvoiceTime($order)
    {
        $lastInvoiceTime = 0;
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoiceTime = strtotime($invoice->getCreatedAt());
            if ($invoiceTime > $lastInvoiceTime) {
                $lastInvoiceTime = $invoiceTime;
            }
        }

        return $lastInvoiceTime;
    }
}
