<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Resolver;

use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Catalog\Model\Product;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

/**
 * Class OrderItem
 *
 * @package Aheadworks\Rma\Model\Request\Resolver
 */
class OrderItem
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var RequestOrderItem
     */
    private $requestOrderItem;

    /**
     * @var array
     */
    private $itemProductUrls = [];

    /**
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param RequestOrderItem $requestOrderItem
     */
    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        RequestOrderItem $requestOrderItem
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->requestOrderItem = $requestOrderItem;
    }

    /**
     * Retrieve order item name
     *
     * @param int $orderItemId
     * @return string
     */
    public function getName($orderItemId)
    {
        return $this->getOrderItemById($orderItemId)->getName();
    }

    /**
     * Retrieve order item sku
     *
     * @param int $orderItemId
     * @return string
     */
    public function getSku($orderItemId)
    {
        return $this->getOrderItemById($orderItemId)->getSku();
    }

    /**
     * Retrieve order item product
     *
     * @param int $orderItemId
     * @return Product
     */
    public function getItemProduct($orderItemId)
    {
        return $this->getOrderItemById($orderItemId)->getProduct();
    }

    /**
     * Retrieve item product url
     *
     * @param int $orderItemId
     * @return string
     */
    public function getItemProductUrl($orderItemId)
    {
        if (!array_key_exists($orderItemId, $this->itemProductUrls)) {
            $item = $this->getOrderItemById($orderItemId);
            $product = $item->getProduct();
            $parentItemId = $item->getParentItemId();
            if ($parentItemId) {
                /** @var Product $parentProduct */
                $parentProduct = $this->getOrderItemById($parentItemId)->getProduct();
                if (in_array($parentProduct->getTypeId(), $this->requestOrderItem->getNotReturnedProductTypes())) {
                    $this->itemProductUrls[$orderItemId] = $parentProduct->getProductUrl();
                }
            } else {
                $this->itemProductUrls[$orderItemId] = $product->getProductUrl();
            }
        }
        return $this->itemProductUrls[$orderItemId];
    }

    /**
     * Retrieve item object for price renderer
     *
     * @param $orderItemId
     * @return \Magento\Sales\Api\Data\OrderItemInterface|\Magento\Sales\Model\Order\Item|null
     */
    public function getItemWithPrice($orderItemId)
    {
        $item = $this->getOrderItemById($orderItemId);
        $parentItem = null;
        if ($item->getParentItemId() && !$item->getParentItem()) {
            $parentItem = $this->getOrderItemById($item->getParentItemId());
        } elseif ($item->getParentItemId() && $item->getParentItem()) {
            $parentItem = $item->getParentItem();
        }

        if ($parentItem && $parentItem->getProductType() == ConfigurableProductType::TYPE_CODE) {
            return $parentItem;
        }

        return $item;
    }

    /**
     * Retrieve item price without discount
     *
     * @param int $orderItemId
     * @return float
     */
    public function getItemPriceWithoutDiscount($orderItemId)
    {
        $item = $this->getItemWithPrice($orderItemId);

        return $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount();
    }

    /**
     * Retrieve order item by id
     *
     * @param int $orderItemId
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     */
    private function getOrderItemById($orderItemId)
    {
        return $this->orderItemRepository->get($orderItemId);
    }
}
