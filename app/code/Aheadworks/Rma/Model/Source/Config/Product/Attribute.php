<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\Config\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Attribute
 *
 * @package Aheadworks\Rma\Model\Source\Config\Product
 */
class Attribute implements OptionSourceInterface
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

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
    private $options;

    /**
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options[] = [
                'value' => null,
                'label' => __('--Please Select--')
            ];
            foreach ($this->getAttributes() as $attribute) {
                $this->options[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => __($attribute->getDefaultFrontendLabel())
                ];
            }
        }
        return $this->options;
    }

    /**
     * Get attributes
     *
     * @return ProductAttributeInterface[]
     */
    private function getAttributes()
    {
        $frontendLabelOrder = $this->sortOrderBuilder
            ->setField(ProductAttributeInterface::FRONTEND_LABEL)
            ->setAscendingDirection()
            ->create();
        $this->searchCriteriaBuilder
            ->addFilter(ProductAttributeInterface::IS_VISIBLE, true)
            ->addFilter(ProductAttributeInterface::IS_FILTERABLE, true)
            ->addFilter(ProductAttributeInterface::FRONTEND_INPUT, 'select')
            ->addFilter(ProductAttributeInterface::BACKEND_TYPE, 'int')
            ->addSortOrder($frontendLabelOrder);
        return $this->productAttributeRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
    }
}
