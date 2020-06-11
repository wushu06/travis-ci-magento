<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\CustomField;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class StatusType
 *
 * @package Aheadworks\Rma\Model\Source\CustomField
 */
class StatusType implements ArrayInterface
{
    /**#@+
     * Constants defined for RMA status types
     */
    const CUSTOMER_VISIBLE = 'customer_visible';
    const CUSTOMER_EDITABLE = 'customer_editable';
    const ADMIN_EDITABLE = 'admin_editable';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CUSTOMER_VISIBLE, 'label' => __('Customer Visible')],
            ['value' => self::CUSTOMER_EDITABLE, 'label' => __('Customer Editable')],
            ['value' => self::ADMIN_EDITABLE, 'label' => __('Admin Editable')]
        ];
    }
}
