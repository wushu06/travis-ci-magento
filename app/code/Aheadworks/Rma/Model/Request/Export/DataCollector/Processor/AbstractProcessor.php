<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector\Processor;

use Aheadworks\Rma\Model\Request\Export\DataCollector\RowHeaderMapper;

/**
 * Class AbstractProcessor
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector\Processor
 */
abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * @var RowHeaderMapper
     */
    protected $rowHeaderMapper;

    /**
     * @param RowHeaderMapper $rowFieldMapper
     */
    public function __construct(
        RowHeaderMapper $rowFieldMapper
    ) {
        $this->rowHeaderMapper = $rowFieldMapper;
        $this->rowHeaderMapper->addRowHeaders($this->prepareRowHeaders());
    }

    /**
     * @inheritdoc
     */
    abstract public function prepareRowData($request, $requestItem, $resultRow);

    /**
     * @inheritdoc
     */
    abstract public function prepareRowHeaders();
}
