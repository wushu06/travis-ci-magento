<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option\Action\ConfigDefault\Converter;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Xml
 *
 * @package Aheadworks\Rma\Model\CustomField\Option\Action\ConfigDefault\Converter
 */
class Xml implements ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        $actions = $source->getElementsByTagName('action');
        foreach ($actions as $action) {
            $actionData = [];
            /** @var $action \DOMElement */
            foreach ($action->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                $actionData[$child->nodeName] = $child->nodeValue;
            }
            $output[] = $actionData;
        }
        return $output;
    }
}
