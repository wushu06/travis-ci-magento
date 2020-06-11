<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CustomField;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Option
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField
 */
class Option extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_rma_custom_field_option', 'id');
    }
}
