<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PostDataProcessor;

/**
 * Interface ProcessorInterface
 *
 * @package Aheadworks\Rma\Model\PostDataProcessor
 */
interface ProcessorInterface
{
    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function process($data);
}
