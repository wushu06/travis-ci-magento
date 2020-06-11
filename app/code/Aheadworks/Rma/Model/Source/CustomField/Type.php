<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\CustomField;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Phrase;

/**
 * Class Type
 *
 * @package Aheadworks\Rma\Model\Source\CustomField
 */
class Type implements ArrayInterface
{
    /**#@+
     * Constants defined for RMA types
     */
    const TEXT = 'text';
    const TEXT_AREA = 'textarea';
    const SELECT = 'select';
    const MULTI_SELECT = 'multiselect';
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
                ['value' => self::TEXT, 'label' => __('Text Field')],
                ['value' => self::TEXT_AREA, 'label' => __('Text Area')],
                ['value' => self::SELECT, 'label' => __('Dropdown')],
                ['value' => self::MULTI_SELECT, 'label' => __('Multiselect')]
            ];
        }
        return $this->optionArray;
    }
}
