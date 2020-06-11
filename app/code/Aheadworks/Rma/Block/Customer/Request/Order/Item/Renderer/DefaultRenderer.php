<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Item as OrderItem;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;
use Aheadworks\Rma\Model\Request\Order as RequestOrder;
use Magento\Sales\Model\Order;
use Magento\Catalog\Block\Product\ImageBuilder as ProductImageBuilder;
use Aheadworks\Rma\Block\CustomField\Input\Renderer\Factory as CustomFieldRendererFactory;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;

/**
 * Class DefaultRenderer
 *
 * @method OrderItem getItem()
 * @method int getRequestStatus()
 * @method CustomFieldInterface[] getCustomFields()
 * @package Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer
 */
class DefaultRenderer extends Template
{
    /**
     * @var RequestOrderItem
     */
    private $requestOrderItem;

    /**
     * @var RequestOrder
     */
    private $requestOrder;

    /**
     * @var ProductImageBuilder
     */
    private $productImageBuilder;

    /**
     * @var CustomFieldRendererFactory
     */
    private $customFieldRendererFactory;

    /**
     * @var OrderItemResolver
     */
    private $orderItemResolver;

    /**
     * @param Context $context
     * @param RequestOrderItem $requestOrderItem
     * @param RequestOrder $requestOrder
     * @param ProductImageBuilder $productImageBuilder
     * @param CustomFieldRendererFactory $customFieldRendererFactory
     * @param OrderItemResolver $orderItemResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestOrderItem $requestOrderItem,
        RequestOrder $requestOrder,
        ProductImageBuilder $productImageBuilder,
        CustomFieldRendererFactory $customFieldRendererFactory,
        OrderItemResolver $orderItemResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->requestOrderItem = $requestOrderItem;
        $this->requestOrder = $requestOrder;
        $this->productImageBuilder = $productImageBuilder;
        $this->customFieldRendererFactory = $customFieldRendererFactory;
        $this->orderItemResolver = $orderItemResolver;
    }

    /**
     * Check can render
     *
     * @return bool
     */
    public function canRender()
    {
        if (empty($this->getItem())) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve item unit price html
     *
     * @return string
     */
    public function getItemPriceHtml()
    {
        /** @var \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $block */
        $block = $this->getLayout()->getBlock('item_unit_price');
        if (!$block) {
            return '';
        }

        $block->setItem($this->orderItemResolver->getItemWithPrice($this->getItem()->getItemId()));

        return $block->toHtml();
    }

    /**
     * Retrieve product view url
     *
     * @return string
     */
    public function getItemProductUrl()
    {
        return $this->orderItemResolver->getItemProductUrl($this->getItem()->getItemId());
    }

    /**
     * Retrieve order item name by id
     *
     * @return string
     */
    public function getItemName()
    {
        return $this->orderItemResolver->getName($this->getItem()->getItemId());
    }

    /**
     * Retrieve order item product
     *
     * @return string
     */
    public function getItemProduct()
    {
        return $this->orderItemResolver->getItemProduct($this->getItem()->getItemId());
    }

    /**
     * Retrieve custom field html
     *
     * @param CustomFieldInterface $customField
     * @return string
     */
    public function getCustomFieldHtml($customField)
    {
        $fieldName = 'order_items.' . $this->getItemNumber() . '.custom_fields.' . $customField->getId();
        $renderer = $this->customFieldRendererFactory->create($customField, $this->getRequestStatus(), $fieldName);

        return $renderer->toHtml();
    }

    /**
     * Retrieve item max count
     *
     * @param OrderItem $item
     * @return int
     */
    public function getItemMaxCount(OrderItem $item)
    {
        return $this->requestOrderItem->getItemMaxCount($item);
    }

    /**
     * Is allowed for order
     *
     * @param Order $order
     * @return bool
     */
    public function isAllowedForOrder(Order $order)
    {
        return $this->requestOrder->isAllowedForOrder($order);
    }

    /**
     * Retrieve item requests
     *
     * @return RequestInterface[]
     */
    public function getRequestsForItem()
    {
        return $this->requestOrderItem->getAllRequestsForOrderItem($this->getItem()->getItemId());
    }

    /**
     * Retrieve request view url
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getRequestViewUrl($request)
    {
        $requestId = !empty($request->getCustomerId()) ? $request->getId() : $request->getExternalLink();
        return $this->getUrl('*/*/view', ['id' => $requestId]);
    }

    /**
     * Retrieve order item product image html
     *
     * @return string
     */
    public function getItemProductImageHtml()
    {
        return $this->productImageBuilder
            ->setProduct($this->orderItemResolver->getItemProduct($this->getItem()->getItemId()))
            ->setImageId('product_small_image')
            ->create()
            ->toHtml();
    }

    /**
     * Check if item available
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function isItemAvailable($item)
    {
        return $this->getItemMaxCount($item) && $this->isAllowedForOrder($item->getOrder());
    }

    /**
     * Retrieve item number
     *
     * @return int
     */
    public function getItemNumber()
    {
        return $this->getItem()->getId();
    }
}
