<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\CustomField;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Phrase;

/**
 * Class Refers
 *
 * @package Aheadworks\Rma\Model\Source\CustomField
 */
class Refers implements ArrayInterface
{
    /**#@+
     * Constants defined for RMA refers
     */
    const REQUEST = 'request';
    // In version 1.2.0 changed the behavior.
    // Now RMA is created not by product items, but by products
    const ITEM = 'item';
    /**#@-*/

    /**
     * @var array
     */
    private $optionArray;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->optionArray) {
            $this->optionArray = [
                ['value' => self::REQUEST, 'label' => __('Request')],
                ['value' => self::ITEM, 'label' => __('Product')]
            ];
        }
        return $this->optionArray;
    }
}
