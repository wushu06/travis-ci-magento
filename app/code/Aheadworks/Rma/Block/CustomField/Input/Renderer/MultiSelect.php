<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;

use Magento\Framework\View\Element\Template;

/**
 * Class MultiSelect
 *
 * @method int[] getValue()
 * @method array getOptions()
 * @method array getDefaultOptions()
 * @method string getFieldName
 * @method string getUid
 * @method string getFieldClass
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
class MultiSelect extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::customfield/input/renderer/multiselect.phtml';

    /**
     * {@inheritdoc}
     */
    public function isSelected($value)
    {
        // If set value
        if (is_array($this->getValue())) {
            return in_array($value, $this->getValue());
        }

        // If new request
        $options = $this->getOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value && in_array($option['value'], $this->getDefaultOptions())) {
                return true;
            }
        }

        return false;
    }
}
