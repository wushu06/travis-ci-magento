<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\ThreadMessage;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Owner
 *
 * @package Aheadworks\Rma\Model\Source\ThreadMessage
 */
class Owner implements ArrayInterface
{
    /**#@+
     * Constants defined for RMA status types
     */
    const ADMIN = '1';
    const CUSTOMER = '2';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ADMIN, 'label' => __('Admin')],
            ['value' => self::CUSTOMER, 'label' => __('Customer')]
        ];
    }
}
