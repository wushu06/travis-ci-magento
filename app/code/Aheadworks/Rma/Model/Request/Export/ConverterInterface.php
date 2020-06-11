<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export;

/**
 * Interface ConverterInterface
 *
 * @package Aheadworks\Rma\Model\Request\Export
 */
interface ConverterInterface
{
    /**
     * Get converted file as result
     *
     * @return array
     */
    public function getFile();
}
