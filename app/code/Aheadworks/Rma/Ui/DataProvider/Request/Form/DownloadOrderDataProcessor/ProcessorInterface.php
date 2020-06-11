<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor;

/**
 * Interface ProcessorInterface
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor
 */
interface ProcessorInterface
{
    /**
     * Prepare data
     *
     * @param array $data
     * @return array
     */
    public function prepare($data);
}
