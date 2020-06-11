<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector\Processor;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;

/**
 * Interface ProcessorInterface
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector
 */
interface ProcessorInterface
{
    /**
     * Prepare row data
     *
     * @param RequestInterface $request
     * @param RequestItemInterface $requestItem
     * @param array $resultRow
     * @return array
     */
    public function prepareRowData($request, $requestItem, $resultRow);

    /**
     * Prepare row headers
     *
     * @return array
     */
    public function prepareRowHeaders();
}
