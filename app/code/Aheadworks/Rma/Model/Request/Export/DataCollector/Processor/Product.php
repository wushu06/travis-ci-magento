<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector\Processor;

use Aheadworks\Rma\Model\Request\Export\DataCollector\RowHeaderInterface;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;
use Aheadworks\Rma\Model\Request\Export\DataCollector\RowHeaderMapper;
use Aheadworks\Rma\Api\Data\RequestItemInterface;

/**
 * Class Product
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector\Processor
 */
class Product extends AbstractProcessor
{
    /**
     * @var OrderItemResolver
     */
    private $orderItemResolver;

    /**
     * @param RowHeaderMapper $rowHeaderMapper
     * @param OrderItemResolver $orderItemResolver
     */
    public function __construct(
        RowHeaderMapper $rowHeaderMapper,
        OrderItemResolver $orderItemResolver
    ) {
        parent::__construct($rowHeaderMapper);
        $this->orderItemResolver = $orderItemResolver;
    }

    /**
     * @inheritdoc
     */
    public function prepareRowData($request, $requestItem, $resultRow)
    {
        $resultRow[$this->rowHeaderMapper->getHeaderPosition(RowHeaderInterface::PRODUCT_SKU)]
            = $this->orderItemResolver->getSku($requestItem[RequestItemInterface::ITEM_ID]);
        $resultRow[$this->rowHeaderMapper->getHeaderPosition(RowHeaderInterface::PRODUCT_NAME)]
            = $this->orderItemResolver->getName($requestItem[RequestItemInterface::ITEM_ID]);
        $resultRow[$this->rowHeaderMapper->getHeaderPosition(RowHeaderInterface::RETURNED_QTY)]
            = $requestItem[RequestItemInterface::QTY];

        return $resultRow;
    }

    /**
     * @inheritdoc
     */
    public function prepareRowHeaders()
    {
        return [
            RowHeaderInterface::PRODUCT_SKU,
            RowHeaderInterface::PRODUCT_NAME,
            RowHeaderInterface::RETURNED_QTY,
        ];
    }
}
