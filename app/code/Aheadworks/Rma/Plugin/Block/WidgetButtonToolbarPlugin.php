<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Plugin\Block;

use Magento\Backend\Block\Widget\Button\Toolbar\Interceptor as ToolbarInterceptor;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aheadworks\Rma\Model\Request\Order as RequestOrder;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;

/**
 * Class WidgetButtonToolbarPlugin
 *
 * @package Aheadworks\Rma\Plugin\Block
 */
class WidgetButtonToolbarPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $url;

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
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param RequestOrder $requestOrder
     * @param RequestOrderItem $requestOrderItem
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $url,
        RequestOrder $requestOrder,
        RequestOrderItem $requestOrderItem,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->request = $request;
        $this->url = $url;
        $this->requestOrder = $requestOrder;
        $this->requestOrderItem = $requestOrderItem;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Add custom button
     *
     * @param ToolbarInterceptor $subject
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     */
    public function beforePushButtons(
        ToolbarInterceptor $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ) {
        $this->addNewRequestButtonToOrderView($buttonList);
    }

    /**
     * Add new request button to order view
     *
     * @param ButtonList $buttonList
     * @return $this
     */
    private function addNewRequestButtonToOrderView($buttonList)
    {
        $orderId = $this->request->getParam('order_id');
        if ($this->request->getFullActionName() == 'sales_order_view' && $orderId
            && $this->isAllowedForOrder($orderId)
        ) {
            $url = $this->url->getUrl('aw_rma_admin/rma/new', ['order_id' => $orderId]);
            $buttonList->add(
                'aw_rma_new_return',
                [
                    'label' => __('New Return'),
                    'onclick' => 'setLocation(\'' . $url . '\')',
                    'class' => 'reset'
                ],
                100
            );
        }

        return $this;
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
        if (!$isAllowedForOrder) {
            return $isAllowedForOrder;
        }

        $orderItems = $this->requestOrderItem->getOrderItemsToRequest($orderId);
        foreach ($orderItems as $orderItem) {
            if ($this->requestOrderItem->getItemMaxCount($orderItem) > 0) {
                return true;
            }
        }

        return false;
    }
}
