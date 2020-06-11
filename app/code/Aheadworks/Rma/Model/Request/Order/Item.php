<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

/**
 * Class Item
 *
 * @package Aheadworks\Rma\Model\Request\Order
 */
class Item
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

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
    private $orderItems = [];

    /**
     * @var array
     */
    private $itemsMaxCount = [];

    /**
     * @var RequestInterface[]
     */
    private $requestByOrderItem = [];

    /**
     * @var array
     */
    private $requestsItemsByOrderId = [];

    /**
     * @param Config $config
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param RequestRepositoryInterface $requestRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        Config $config,
        OrderItemRepositoryInterface $orderItemRepository,
        RequestRepositoryInterface $requestRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->config = $config;
        $this->orderItemRepository = $orderItemRepository;
        $this->requestRepository = $requestRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Retrieve parent order items for request to render products
     *
     * @param int $orderId
     * @return \Magento\Sales\Model\Order\Item[]|OrderItemInterface[]
     */
    public function getParentOrderItemsToRequest($orderId)
    {
        $preparedItems = [];
        $items = $this->getItemsByOrder($orderId);
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $preparedItems[] = $item;
        }

        return $preparedItems;
    }

    /**
     * Retrieve order items to request
     *
     * @param int $orderId
     * @return \Magento\Sales\Model\Order\Item[]|OrderItemInterface[]
     */
    public function getOrderItemsToRequest($orderId)
    {
        $ignoreItems = [];
        $preparedItems = [];
        $items = $this->getItemsByOrder($orderId);
        foreach ($items as $item) {
            if ($item->getProductType() == ConfigurableProductType::TYPE_CODE
                || $this->isBundleDynamicPrice($item)
                || in_array($item->getItemId(), $ignoreItems)
            ) {
                continue;
            }
            if ($this->isBundleFixedPrice($item)) {
                foreach ($item->getChildrenItems() as $childrenItem) {
                    $ignoreItems[] = $childrenItem->getItemId();
                }
            }
            $preparedItems[] = $item;
        }

        return $preparedItems;
    }

    /**
     * Retrieves maximal order item count available for RMA
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param bool|int $excludeRequestId
     * @return int
     */
    public function getItemMaxCount($item, $excludeRequestId = false)
    {
        if (!isset($this->itemsMaxCount[$item->getId()])) {
            $max = 0;
            if ($this->isBundleDynamicPrice($item) && $item->getChildrenItems()) {
                /** @var \Magento\Sales\Model\Order\Item $childrenItem */
                foreach ($item->getChildrenItems() as $childrenItem) {
                    $childrenMax = $childrenItem->getQtyInvoiced() - $childrenItem->getQtyRefunded();
                    $requestItems = $this->getAllRequestItems($item->getOrderId(), $excludeRequestId);
                    foreach ($requestItems as $requestItem) {
                        if ($requestItem->getItemId() == $childrenItem->getId()) {
                            $childrenMax -= $requestItem->getQty();
                        }
                    }
                    $max += $childrenMax;
                }
            } else {
                $max = $excludeRequestId
                    ? $item->getQtyInvoiced()
                    : $item->getQtyInvoiced() - $item->getQtyRefunded();
                $requestItems = $this->getAllRequestItems($item->getOrderId(), $excludeRequestId);
                foreach ($requestItems as $requestItem) {
                    if ($requestItem->getItemId() == $item->getId()) {
                        $max -= $requestItem->getQty();
                    }
                }
            }
            $this->itemsMaxCount[$item->getId()] = max($max, 0);
        }

        return $this->itemsMaxCount[$item->getId()];
    }

    /**
     * Retrieves request collection for given order item
     *
     * @param int $itemId
     * @return RequestInterface[]
     */
    public function getAllRequestsForOrderItem($itemId)
    {
        if (!isset($this->requestByOrderItem[$itemId])) {
            $this->searchCriteriaBuilder->addFilter(RequestItemInterface::ITEM_ID, $itemId);

            $this->requestByOrderItem[$itemId] = $this->requestRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();
        }

        return $this->requestByOrderItem[$itemId];
    }

    /**
     * Retrieves order item product types, for which return creation is not allowed.
     * (Means, that it contains child items allowed for return)
     *
     * @return array
     */
    public function getNotReturnedProductTypes()
    {
        return [
            BundleProductType::TYPE_CODE,
            ConfigurableProductType::TYPE_CODE
        ];
    }

    /**
     * Retrieves request items for given order id
     *
     * @param int $orderId
     * @param bool|int $excludeRequestId
     * @return RequestItemInterface[]
     */
    private function getAllRequestItems($orderId, $excludeRequestId = false)
    {
        $cacheKey = implode('-', [$orderId, (int)$excludeRequestId]);
        if (!isset($this->requestsItemsByOrderId[$cacheKey])) {
            $this->searchCriteriaBuilder->addFilter(RequestInterface::ORDER_ID, $orderId);
            if ($excludeRequestId) {
                $this->searchCriteriaBuilder->addFilter(RequestInterface::ID, $excludeRequestId, 'nin');
            }
            $requestItems = $this->requestRepository->getList($this->searchCriteriaBuilder->create())->getItems();

            $requestOrderItems = [];
            foreach ($requestItems as $requestItem) {
                $requestOrderItems = array_merge($requestOrderItems, $requestItem->getOrderItems());
            }
            $this->requestsItemsByOrderId[$cacheKey] = $requestOrderItems;
        }

        return $this->requestsItemsByOrderId[$cacheKey];
    }

    /**
     * Check if dynamic price in bundle
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    private function isBundleDynamicPrice($item)
    {
        return !$this->isBundleFixedPrice($item);
    }

    /**
     * Check if fixed price in bundle
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    private function isBundleFixedPrice($item)
    {
        // For dynamic bundle
        if ($item->getProductType() == BundleProductType::TYPE_CODE
            && ($item->getChildrenItems() && $item->isChildrenCalculated())
        ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve order items to request
     *
     * @param int $orderId
     * @return \Magento\Sales\Model\Order\Item[]|OrderItemInterface[]
     */
    private function getItemsByOrder($orderId)
    {
        if (!isset($this->orderItems[$orderId])) {
            $this->searchCriteriaBuilder->addFilter(OrderItemInterface::ORDER_ID, $orderId);
            $this->orderItems[$orderId] = $this->orderItemRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();
        }

        return $this->orderItems[$orderId];
    }
}
