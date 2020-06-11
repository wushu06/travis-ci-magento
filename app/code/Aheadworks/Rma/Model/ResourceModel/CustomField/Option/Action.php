<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CustomField\Option;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Action
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField\Option
 */
class Action extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('aw_rma_custom_field_option_action', 'id');
    }
}
