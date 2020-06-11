<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector\Processor;

use Aheadworks\Rma\Model\Request\Export\DataCollector\RowHeaderMapper;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;
use Aheadworks\Rma\Model\Config;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;

/**
 * Class ManufacturerAttribute
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector\Processor
 */
class ManufacturerAttribute extends AbstractProcessor
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderItemResolver
     */
    private $orderItemResolver;

    /**
     * @param RowHeaderMapper $headerMapper
     * @param Config $config
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param OrderItemResolver $orderItemResolver
     */
    public function __construct(
        RowHeaderMapper $headerMapper,
        Config $config,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        OrderItemResolver $orderItemResolver
    ) {
        $this->config = $config;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->orderItemResolver = $orderItemResolver;
        parent::__construct($headerMapper);
    }

    /**
     * @inheritdoc
     */
    public function prepareRowData($request, $requestItem, $resultRow)
    {
        $attribute = $this->getProductAttribute();
        if ($attribute) {
            $product = $this->orderItemResolver->getItemProduct($requestItem[RequestItemInterface::ITEM_ID]);
            if ($product) {
                $rowFieldPosition = $this->rowHeaderMapper->getHeaderPosition($attribute->getDefaultFrontendLabel());
                $attributeValue = $product->getAttributeText($attribute->getAttributeCode()) ? : '';
                $resultRow[$rowFieldPosition] = $attributeValue;
            }
        }

        return $resultRow;
    }

    /**
     * @inheritdoc
     */
    public function prepareRowHeaders()
    {
        $rowHeaders = [];
        $attribute = $this->getProductAttribute();
        if ($attribute) {
            $rowHeaders[] = $attribute->getDefaultFrontendLabel();
        }

        return $rowHeaders;
    }

    /**
     * Get product attribute
     *
     * @return ProductAttributeInterface|bool
     */
    private function getProductAttribute()
    {
        $attribute = false;
        $attributeCode = $this->config->getManufacturerProductAttributeCode();
        if ($attributeCode) {
            try {
                $attribute = $this->productAttributeRepository->get($attributeCode);
            } catch (NoSuchEntityException $exception) {
            }
        }

        return $attribute;
    }
}
