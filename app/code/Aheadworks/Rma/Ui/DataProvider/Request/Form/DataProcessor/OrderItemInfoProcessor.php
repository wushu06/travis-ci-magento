<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor;

use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;
use Magento\CatalogInventory\Model\StockRegistryProvider;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;

/**
 * Class OrderItemInfoProcessor
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor
 */
class OrderItemInfoProcessor
{
    /**
     * @var OrderItemResolver
     */
    private $orderItemResolver;

    /**
     * @var StockRegistryProvider
     */
    private $stockRegistryProvider;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param OrderItemResolver $orderResolver
     * @param StockRegistryProvider $stockRegistryProvider
     * @param PriceCurrencyInterface $priceCurrency
     * @param UrlInterface $url
     */
    public function __construct(
        OrderItemResolver $orderResolver,
        StockRegistryProvider $stockRegistryProvider,
        PriceCurrencyInterface $priceCurrency,
        UrlInterface $url
    ) {
        $this->orderItemResolver = $orderResolver;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->priceCurrency = $priceCurrency;
        $this->url = $url;
    }

    /**
     * Process order items data
     *
     * @param array $orderItems
     * @param int $storeId
     * @return array
     */
    public function process($orderItems, $storeId)
    {
        foreach ($orderItems as &$orderItem) {
            $orderItemId = $orderItem['item_id'];
            $orderItem['id_prop'] =  isset($orderItem['id']) ? $orderItem['id'] : $orderItem['id_prop'];
            $orderItem['name_label'] = $this->orderItemResolver->getName($orderItemId);
            if ($product = $this->orderItemResolver->getItemProduct($orderItemId)) {
                $orderItem['name_url'] = $this->getUrl(
                    'catalog/product/edit',
                    ['id' => $product->getEntityId()]
                );
                $stock = $this->stockRegistryProvider->getStockItem($product->getEntityId(), $storeId);
                $orderItem['qty_in_stock'] = $stock ? $stock->getQty() : 0;
                // In the future, if not order
                //$orderItem['price'] = $this->priceCurrency->format($product->getPrice(), false);
            }
            $orderItem['price'] = $this->priceCurrency->format(
                $this->orderItemResolver->getItemWithPrice($orderItemId)->getBasePrice(),
                false
            );
            $orderItem['sku'] = $this->orderItemResolver->getSku($orderItemId);
            $orderItem['total_paid'] = $this->priceCurrency->format(
                $this->orderItemResolver->getItemPriceWithoutDiscount($orderItemId),
                false
            );
        }

        return $orderItems;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl($route = '', $params = [])
    {
        return $this->url->getUrl($route, $params);
    }
}
