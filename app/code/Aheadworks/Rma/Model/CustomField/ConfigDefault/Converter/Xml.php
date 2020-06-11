<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\ConfigDefault\Converter;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Xml
 *
 * @package Aheadworks\Rma\Model\CustomField\ConfigDefault\Converter
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

        $customFields = $source->getElementsByTagName('custom_field');
        foreach ($customFields as $customField) {
            $customFieldData = [];
            /** @var $customField \DOMElement */
            foreach ($customField->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                $statusesType = ['visible_for_status_ids', 'editable_for_status_ids', 'editable_admin_for_status_ids'];
                /** @var $customField \DOMElement */
                if (in_array($child->nodeName, $statusesType)) {
                    $this->collectStatusesData($child, $customFieldData);
                } elseif ($child->nodeName == 'attributes') {
                    $this->collectAttrData($child, $customFieldData);
                } elseif ($child->nodeName == 'option') {
                    $this->collectOptionData($child, $customFieldData);
                } else {
                    $customFieldData[$child->nodeName] = $child->nodeValue;
                }
            }
            $output[] = $customFieldData;
        }
        return $output;
    }

    /**
     * Collect attribute data
     *
     * @param \DOMElement $node
     * @param array $customFieldData
     * @return void
     */
    protected function collectAttrData($node, &$customFieldData)
    {
        foreach ($node->childNodes as $attrNode) {
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
            $customFieldData[$attrNode->nodeName] = $params;
        }
    }

    /**
     * Collect statuses data
     *
     * @param \DOMElement $node
     * @param array $customFieldData
     * @return void
     */
    protected function collectStatusesData($node, &$customFieldData)
    {
        $statusesData = [];
        foreach ($node->childNodes as $statusNode) {
            if (!$statusNode instanceof \DOMElement || $statusNode->nodeName != 'id') {
                continue;
            }
            $statusesData[] = $statusNode->nodeValue;
        }
        $customFieldData[$node->nodeName] = $statusesData;
    }

    /**
     * Collect option data
     *
     * @param \DOMElement $node
     * @param array $customFieldData
     * @return void
     */
    private function collectOptionData($node, &$customFieldData)
    {
        $optionData = [];
        foreach ($node->childNodes as $optionNode) {
            if (!$optionNode instanceof \DOMElement) {
                continue;
            }
            $optionData[$optionNode->nodeName] = $optionNode->nodeValue;
        }
        $customFieldData[$node->nodeName][] = $optionData;
    }
}
