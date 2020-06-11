<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PostDataProcessor;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Class PrintLabel
 *
 * @package Aheadworks\Rma\Model\Request\PostDataProcessor
 */
class PrintLabel implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        $data = $this->preparePrintLabel($data);

        return $data;
    }

    /**
     * Prepare print label
     *
     * @param array $data
     * @return array
     */
    private function preparePrintLabel($data)
    {
        if (!isset($data[RequestInterface::PRINT_LABEL])
            || !isset($data[RequestInterface::PRINT_LABEL][CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])
        ) {
            return $data;
        }
        $customAttributes = $data[RequestInterface::PRINT_LABEL][CustomAttributesDataInterface::CUSTOM_ATTRIBUTES];

        $preparedCustomAttr = [];
        foreach ($customAttributes as $customAttributeCode => $customAttributeValue) {
            $preparedCustomAttr[] = [
                AttributeInterface::ATTRIBUTE_CODE => $customAttributeCode,
                AttributeInterface::VALUE => $customAttributeValue,
            ];
        }
        $data[RequestInterface::PRINT_LABEL][CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] = $preparedCustomAttr;

        return $data;
    }
}
