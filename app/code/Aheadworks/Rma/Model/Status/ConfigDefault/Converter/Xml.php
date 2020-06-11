<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status\ConfigDefault\Converter;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Xml
 *
 * @package Aheadworks\Rma\Model\Status\ConfigDefault\Converter
 */
class Xml implements ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        $statuses = $source->getElementsByTagName('status');
        foreach ($statuses as $status) {
            $statusData = [];
            /** @var $status \DOMElement */
            foreach ($status->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                /** @var $child \DOMElement */
                if ($child->nodeName == 'attributes') {
                    foreach ($child->childNodes as $attrNode) {
                        if (!$attrNode instanceof \DOMElement) {
                            continue;
                        }
                        $params = [];
                        /** @var $attrNode \DOMElement */
                        foreach ($attrNode->childNodes as $attrParam) {
                            if (!$attrParam instanceof \DOMElement) {
                                continue;
                            }
                            $params[$attrParam->nodeName] = trim($attrParam->nodeValue);
                        }
                        if ($attrNode->nodeName == 'email_template') {
                            $statusData[$attrNode->nodeName][] = $params;
                        } else {
                            $statusData[$attrNode->nodeName] = $params;
                        }
                    }
                } else {
                    $statusData[$child->nodeName] = $child->nodeValue;
                }
            }
            $output[] = $statusData;
        }
        return $output;
    }
}
