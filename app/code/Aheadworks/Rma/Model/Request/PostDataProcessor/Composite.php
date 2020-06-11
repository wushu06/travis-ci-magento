<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PostDataProcessor;

/**
 * Class Composite
 *
 * @package Aheadworks\Rma\Model\Request\PostDataProcessor
 */
class Composite
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data)
    {
        foreach ($this->processors as $processor) {
            $data = $processor->process($data);
        }

        return $data;
    }
}
