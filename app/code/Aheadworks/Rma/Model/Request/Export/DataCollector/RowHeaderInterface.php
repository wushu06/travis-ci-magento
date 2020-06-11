<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector;

/**
 * Interface RowHeaderInterface
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector
 */
interface RowHeaderInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const REQUEST_ID = 'Request #';
    const CREATED_AT = 'Created At';
    const PRODUCT_SKU = 'Product SKU';
    const PRODUCT_NAME = 'Product Name';
    const RETURNED_QTY = 'Returned Qty';
    /**#@-*/
}
