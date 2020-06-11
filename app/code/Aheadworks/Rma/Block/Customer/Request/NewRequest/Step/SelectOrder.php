<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\NewRequest\Step;

use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Renderer\CmsBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Rma\Model\Request\Order as RequestOrder;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer\Factory as ItemRendererFactory;

/**
 * Class SelectOrder
 *
 * @package Aheadworks\Rma\Block\Customer\Request
 */
class SelectOrder extends Template
{
    /**
     * @var string
     */
    protected $_template = 'customer/request/newrequest/step/selectorder.phtml';

    /**
     * @var CmsBlock
     */
    private $cmsBlockRenderer;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RequestOrder
     */
    private $requestOrder;

    /**
     * @var RequestOrderItem
     */
    private $requestOrderItem;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ItemRendererFactory
     */
    private $itemRendererFactory;

    /**
     * @param Context $context
     * @param CmsBlock $cmsBlockRenderer
     * @param Config $config
     * @param RequestOrder $requestOrder
     * @param RequestOrderItem $requestOrderItem
     * @param CustomerSession $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param ItemRendererFactory $itemRendererFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CmsBlock $cmsBlockRenderer,
        Config $config,
        RequestOrder $requestOrder,
        RequestOrderItem $requestOrderItem,
        CustomerSession $customerSession,
        PriceCurrencyInterface $priceCurrency,
        ItemRendererFactory $itemRendererFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cmsBlockRenderer = $cmsBlockRenderer;
        $this->config = $config;
        $this->requestOrder = $requestOrder;
        $this->requestOrderItem = $requestOrderItem;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->itemRendererFactory = $itemRendererFactory;
    }

    /**
     * Retrieve order items to request
     *
     * @param int $orderId
     * @return \Magento\Sales\Model\Order\Item[]|OrderItemInterface[]
     */
    public function getOrderItemsToRequest($orderId)
    {
        return $this->requestOrderItem->getParentOrderItemsToRequest($orderId);
    }

    /**
     * Retrieve product selection block html
     *
     * @return string
     */
    public function getProductSelectionBlockHtml()
    {
        return $this->cmsBlockRenderer->render($this->config->getProductSelectionBlock());
    }

    /**
     * Retrieve customer orders
     *
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->requestOrder->getOrders($this->customerSession->getCustomerId());
    }

    /**
     * Retrieve order info
     *
     * @param Order $order
     * @return string
     */
    public function getOrderInfo(Order $order)
    {
        if (!$this->isAllowedForOrder($order)) {
            return __('Can\'t create return for this order');
        }
        return '';
    }

    /**
     * Check is allowed for order or not
     *
     * @param Order $order
     * @return bool
     */
    public function isAllowedForOrder(Order $order)
    {
        $isAllowedForOrder = $this->requestOrder->isAllowedForOrder($order);
        if (!$isAllowedForOrder) {
            return $isAllowedForOrder;
        }

        $orderItems = $this->requestOrderItem->getOrderItemsToRequest($order->getEntityId());
        foreach ($orderItems as $orderItem) {
            if ($this->requestOrderItem->getItemMaxCount($orderItem) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get item renderer html
     *
     * @param OrderItem $orderItem
     * @return string
     */
    public function getItemRendererHtml(OrderItem $orderItem)
    {
        $block = $this->itemRendererFactory->create($orderItem, 'select_order');
        return $block->toHtml();
    }

    /**
     * Retrieve no orders message
     *
     * @return \Magento\Framework\Phrase
     */
    public function getNoOrdersMessage()
    {
        if ($this->config->getReturnPeriod() > 0) {
            $message = __(
                'You have no completed orders to request RMA or your orders were placed more than %1 days ago.',
                $this->config->getReturnPeriod()
            );
        } else {
            $message = __('You have no completed orders to request RMA');
        }
        return $message;
    }

    /**
     * Convert and format price
     *
     * @param float $amount
     * @return string
     */
    public function convertAndFormatPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
    }

    /**
     * Retrieve submit url
     *
     * @param int $orderId
     * @return string
     */
    public function getSubmitUrl($orderId)
    {
        return $this->getUrl('*/*/createRequestStep', ['order_id' => $orderId]);
    }

    /**
     * Retrieves selected order ID
     *
     * @return int
     */
    public function getCurrentOrderId()
    {
        return $this->customerSession->getAwRmaRequestOrderId() ? : 0;
    }
}
