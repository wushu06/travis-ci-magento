<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\View;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Block\Product\ImageBuilder as ProductImageBuilder;
use Aheadworks\Rma\Block\CustomField\Input\Renderer\Factory as CustomFieldRendererFactory;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;

/**
 * Class Items
 *
 * @method RequestItemInterface[] getOrderItems()
 * @method Items setOrderItems(RequestItemInterface[] $orderItems)
 * @method int getStatusId()
 * @method Items setStatusId(int $statusId)
 * @package Aheadworks\Rma\Block\Customer\Request\View
 */
class Items extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/view/items.phtml';

    /**
     * @var ProductImageBuilder
     */
    private $productImageBuilder;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

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
     * @param ProductImageBuilder $productImageBuilder
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param CustomFieldRendererFactory $customFieldRendererFactory
     * @param OrderItemResolver $orderItemResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductImageBuilder $productImageBuilder,
        CustomFieldRepositoryInterface $customFieldRepository,
        CustomFieldRendererFactory $customFieldRendererFactory,
        OrderItemResolver $orderItemResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productImageBuilder = $productImageBuilder;
        $this->customFieldRepository = $customFieldRepository;
        $this->customFieldRendererFactory = $customFieldRendererFactory;
        $this->orderItemResolver = $orderItemResolver;
    }

    /**
     * Retrieve order item product
     *
     * @param int $itemId
     * @return string
     */
    public function getItemProduct($itemId)
    {
        return $this->orderItemResolver->getItemProduct($itemId);
    }

    /**
     * Retrieve order item product image html
     *
     * @param int $itemId
     * @return string
     */
    public function getItemProductImageHtml($itemId)
    {
        return $this->productImageBuilder->setProduct($this->orderItemResolver->getItemProduct($itemId))
            ->setImageId('product_thumbnail_image')
            ->create()
            ->toHtml();
    }

    /**
     * Retrieve item unit price html
     *
     * @param int $itemId
     * @return string
     */
    public function getItemPriceHtml($itemId)
    {
        /** @var \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $block */
        $block = $this->getLayout()->getBlock('item_unit_price');
        if (!$block) {
            return '';
        }
        $block->setItem($this->orderItemResolver->getItemWithPrice($itemId));

        return $block->toHtml();
    }

    /**
     * Retrieve product view url
     *
     * @param int $itemId
     * @return string
     */
    public function getItemProductUrl($itemId)
    {
        return $this->orderItemResolver->getItemProductUrl($itemId);
    }

    /**
     * Retrieve order item name by id
     *
     * @param int $itemId
     * @return string
     */
    public function getItemName($itemId)
    {
        return $this->orderItemResolver->getName($itemId);
    }

    /**
     * Retrieve request custom fields input html
     *
     * @param RequestCustomFieldValueInterface $requestItemCustomField
     * @param int $requestItemId
     * @return string
     */
    public function getRequestItemCustomFieldHtml($requestItemCustomField, $requestItemId)
    {
        $customField = $this->customFieldRepository->get($requestItemCustomField->getFieldId());
        $fieldName = 'order_items.' . $requestItemId . '.custom_fields.' . $customField->getId();
        $value = $requestItemCustomField->getValue();
        $renderer = $this->customFieldRendererFactory
            ->create($customField, $this->getStatusId(), $fieldName, $value);

        return $renderer->toHtml();
    }
}
