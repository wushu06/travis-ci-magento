<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;

use Magento\Framework\View\Element\Template;

/**
 * Class Text
 *
 * @method string getValue()
 * @method string getFieldName
 * @method string getUid
 * @method string getFieldClass
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
class Text extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::customfield/input/renderer/text.phtml';
}
