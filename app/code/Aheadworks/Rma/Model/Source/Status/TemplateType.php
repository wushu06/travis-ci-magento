<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\Status;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TemplateType
 *
 * @package Aheadworks\Rma\Model\Source\Status
 */
class TemplateType implements ArrayInterface
{
    /**#@+
     * Constants defined for RMA status types
     */
    const ADMIN = 'admin';
    const CUSTOMER = 'customer';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ADMIN, 'label' => __('Admin Template')],
            ['value' => self::CUSTOMER, 'label' => __('Customer Template')]
        ];
    }
}
