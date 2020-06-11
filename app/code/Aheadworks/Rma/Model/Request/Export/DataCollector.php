<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Aheadworks\Rma\Model\Request\Export\DataCollector\RowHeaderMapper;
use Aheadworks\Rma\Model\Request\Export\DataCollector\Processor\ProcessorInterface;
use Aheadworks\Rma\Model\Request\Export\DataCollector\Processor\Request;
use Aheadworks\Rma\Model\Request\Export\DataCollector\Processor\CustomField;
use Aheadworks\Rma\Model\Request\Export\DataCollector\Processor\Product;
use Aheadworks\Rma\Model\Request\Export\DataCollector\Processor\ManufacturerAttribute;
use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;

/**
 * Class DataCollector
 *
 * @package Aheadworks\Rma\Model\Request\Export
 */
class DataCollector
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var RowHeaderMapper
     */
    private $rowFieldMapper;

    /**
     * @var array
     */
    private $processors = [
        'Request Processor' => Request::class,
        'Product Processor' => Product::class,
        'Custom Field Processor' => CustomField::class,
        'Manufacturer Attribute Processor' => ManufacturerAttribute::class
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param RowHeaderMapper $rowFieldMapper
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        RowHeaderMapper $rowFieldMapper
    ) {
        $this->objectManager = $objectManager;
        $this->rowFieldMapper = $rowFieldMapper;
    }

    /**
     * Retrieve headers row array for Export
     *
     * @return array
     */
    public function getHeaders()
    {
        foreach ($this->processors as $processorName => $processorClass) {
            $instance = $this->objectManager->get($processorClass);
            if ($instance instanceof ProcessorInterface) {
                $instance->prepareRowHeaders();
            }
        }
        return $this->rowFieldMapper->getHeaders();
    }

    /**
     * Returns rows data
     *
     * @param DocumentInterface|RequestInterface $document
     * @param array $headers
     * @return array
     */
    public function getRowsData(DocumentInterface $document, $headers)
    {
        $rows = [];
        $orderItems = $document->getOrderItems();
        foreach ($orderItems as $orderItem) {
            $row = $this->getRowData($document, $orderItem);
            $rows[] = $this->adjustRowStructure($row, $headers);
        }

        return $rows;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface|RequestInterface $document
     * @param array|RequestItemInterface $orderItem
     * @return array
     */
    private function getRowData(DocumentInterface $document, $orderItem)
    {
        $row = [];
        foreach ($this->processors as $processorName => $processorClass) {
            $instance = $this->objectManager->get($processorClass);
            if ($instance instanceof ProcessorInterface) {
                $row = $instance->prepareRowData($document, $orderItem, $row);
            }
        }

        return $row;
    }

    /**
     * Check a row and fill up gaps with empty values
     *
     * @param array $row
     * @param array $headers
     * @return array
     */
    private function adjustRowStructure($row, $headers)
    {
        foreach ($headers as $index => $header) {
            $row[$index] = isset($row[$index]) ? $row[$index] : '';
        }

        ksort($row);
        return $row;
    }
}
