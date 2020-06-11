<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\CustomField;

use Magento\Framework\View\Element\Template;

/**
 * Class Wrapper
 *
 * @method bool getIsVisible()
 * @method string getLabel()
 * @method string getUid()
 * @method string getAdditionalClasses()
 * @package Aheadworks\Rma\Block\CustomField
 */
class Wrapper extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::customfield/wrapper.phtml';

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if (!$this->getIsVisible()) {
            return '';
        }

        return parent::toHtml();
    }
}
