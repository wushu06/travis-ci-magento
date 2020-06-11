<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Renderer\Backend\Grid;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionInterface;
use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class Mapper
 *
 * @package Aheadworks\Rma\Model\CustomField\Renderer\Backend\Grid
 */
class Mapper
{
    /**
     * @var array
     */
    private $gridElementMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select'
    ];

    /**
     * @var array
     */
    private $dataTypeElementMap = [
        Type::TEXT => 'text',
        Type::TEXT_AREA => 'text',
        Type::SELECT => 'select',
        Type::MULTI_SELECT => 'select'
    ];

    /**
     * @var array
     */
    private $propertiesMap = [
        'label' => 'getStorefrontLabel'
    ];

    /**
     * Map custom field attribute
     *
     * @param CustomFieldInterface $customField
     * @return array
     */
    public function map($customField)
    {
        $result = [];
        foreach ($this->propertiesMap as $sourceFieldName => $methodName) {
            $result[$sourceFieldName] = $customField->$methodName();
        }
        if (count($customField->getOptions())) {
            $result['options'] = $this->prepareOptions($customField->getOptions());
        }
        $type = $customField->getType();
        $result['dataType'] = isset($this->dataTypeElementMap[$type])
                ? $this->dataTypeElementMap[$type]
                : $type;
        $result['component'] = isset($this->gridElementMap[$result['dataType']])
            ? $this->gridElementMap[$result['dataType']]
            : $result['dataType'];
        $result['filter'] = $result['dataType'];
        $result['visible'] = false;

        return $result;
    }

    /**
     * Prepare options data
     *
     * @param CustomFieldOptionInterface[] $options
     * @return array
     */
    private function prepareOptions($options)
    {
        $optionsData = [];
        foreach ($options as $option) {
            $optionsData[] = [
                'value' => $option->getId(),
                'label' => $option->getStorefrontLabel()
            ];
        }

        return $optionsData;
    }
}
