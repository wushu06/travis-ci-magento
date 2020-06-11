<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;

use Magento\Framework\View\Element\Template;

/**
 * Class TextArea
 *
 * @method string getValue()
 * @method string getFieldName
 * @method string getUid
 * @method string getFieldClass
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
class TextArea extends Template
{
    /**
     * Default number of rows
     */
    const DEFAULT_ROWS = 5;

    /**
     * Default number of columns
     */
    const DEFAULT_COLS = 15;

    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::customfield/input/renderer/textarea.phtml';

    /**
     * Retrieve rows
     *
     * @return int
     */
    public function getRows()
    {
        if (!$this->hasData('rows')) {
            $this->setData('rows', self::DEFAULT_ROWS);
        }
        return $this->getData('rows');
    }

    /**
     * Retrieve cols
     *
     * @return int
     */
    public function getCols()
    {
        if (!$this->hasData('cols')) {
            $this->setData('cols', self::DEFAULT_COLS);
        }
        return $this->getData('cols');
    }
}
