<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor;

use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;
use Magento\Sales\Model\Order\Item;
use Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\OrderItemInfoProcessor;

/**
 * Class OrderItems
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor
 */
class OrderItems implements ProcessorInterface
{
    /**
     * @var Composite
     */
    private $requestOrderItem;

    /**
     * @var OrderItemInfoProcessor
     */
    private $orderItemInfoProcessor;

    /**
     * @param RequestOrderItem $requestOrderItem
     * @param OrderItemInfoProcessor $orderItemInfoProcessor
     */
    public function __construct(
        RequestOrderItem $requestOrderItem,
        OrderItemInfoProcessor $orderItemInfoProcessor
    ) {
        $this->requestOrderItem = $requestOrderItem;
        $this->orderItemInfoProcessor = $orderItemInfoProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($data)
    {
        $orderId = $data['order_id'];
        $storeId = $data['store_id'];
        $orderItems = $this->requestOrderItem->getOrderItemsToRequest($orderId);
        $data['order_items'] = $this->prepareOrderItems($orderItems, $storeId);

        return $data;
    }

    /**
     * Prepare order items
     *
     * @param Item[] $orderItems
     * @param int $storeId
     * @return array
     */
    private function prepareOrderItems($orderItems, $storeId)
    {
        $prepareOrderItems = [];
        $idProp = 1;
        foreach ($orderItems as $orderItem) {
            if ($qtyAvailable = $this->requestOrderItem->getItemMaxCount($orderItem)) {
                $prepareOrderItems[] = [
                    'item_id' => $orderItem->getItemId(),
                    'id_prop' => $idProp,
                    'qty'     => $qtyAvailable
                ];
                $idProp++;
            }
        }

        return $this->orderItemInfoProcessor->process($prepareOrderItems, $storeId);
    }
}
