<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor;

/**
 * Class Composite
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor
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
     * Prepare data
     *
     * @param array $data
     * @return array
     */
    public function prepare($data)
    {
        foreach ($this->processors as $processor) {
            $data = $processor->prepare($data);
        }
        return $data;
    }
}
