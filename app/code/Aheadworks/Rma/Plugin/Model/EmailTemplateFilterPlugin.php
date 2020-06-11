<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Plugin\Model;

use Magento\Email\Model\Template\Filter;

/**
 * Class EmailTemplateFilterPlugin
 *
 * @package Aheadworks\Rma\Plugin\Model
 */
class EmailTemplateFilterPlugin
{
    /**
     * Double template rendering
     *
     * @param Filter $subject
     * @param \Closure $proceed
     * @param string $value
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundFilter($subject, \Closure $proceed, $value)
    {
        $isTriggeredSecond = is_string($value) && !empty($value)
            ? strpos($value, 'request.getCustomText()') !== false
            : false;

        $value = $proceed($value);
        if ($isTriggeredSecond) {
            $value = $proceed($value);
        }

        return $value;
    }
}
