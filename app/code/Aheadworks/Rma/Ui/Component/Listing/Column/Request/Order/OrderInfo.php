<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Listing\Column\Request\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Rma\Model\Request\Order as RequestOrder;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;

/**
 * Class OrderInfo
 *
 * @package Aheadworks\Rma\Ui\Component\Listing\Column\Request\Order
 */
class OrderInfo extends Column
{
    /**
     * @var RequestOrder
     */
    private $requestOrder;

    /**
     * @var RequestOrderItem
     */
    private $requestOrderItem;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param RequestOrder $requestOrder
     * @param RequestOrderItem $requestOrderItem
     * @param OrderRepositoryInterface $orderRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RequestOrder $requestOrder,
        RequestOrderItem $requestOrderItem,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->requestOrder = $requestOrder;
        $this->requestOrderItem = $requestOrderItem;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $messages = [];
                $orderId = $item['entity_id'];
                $isAllowedForOrder = $this->isAllowedForOrder($orderId);
                $isAllowedOrderItems = $this->isAllowedOrderItems($orderId);

                if (!$isAllowedForOrder) {
                    $messages[] = __('Can\'t create return for this order');
                }
                if (!$isAllowedOrderItems) {
                    $messages[] = __('Can\'t create return for this order items');
                }
                $item[$fieldName] = implode('<br/>', $messages);
                $item['is_available_order'] = $isAllowedForOrder && $isAllowedOrderItems;
            }
        }
        return $dataSource;
    }

    /**
     * Check is allowed for order or not
     *
     * @param int $orderId
     * @return bool
     */
    private function isAllowedForOrder($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $isAllowedForOrder = $this->requestOrder->isAllowedForOrder($order);

        return $isAllowedForOrder;
    }

    /**
     * Check is allowed order items
     *
     * @param int $orderId
     * @return bool
     */
    private function isAllowedOrderItems($orderId)
    {
        $orderItems = $this->requestOrderItem->getOrderItemsToRequest($orderId);
        foreach ($orderItems as $orderItem) {
            if ($this->requestOrderItem->getItemMaxCount($orderItem) > 0) {
                return true;
            }
        }

        return false;
    }
}
