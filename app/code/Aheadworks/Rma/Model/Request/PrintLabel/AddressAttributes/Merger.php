<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\AddressAttributes;

/**
 * Class Merger
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\AddressAttributes
 */
class Merger
{
    /**
     * @var array
     */
    private $formElementMap = [
        'checkbox' => 'Magento_Ui/js/form/element/select',
        'select' => 'Magento_Ui/js/form/element/select',
        'textarea'  => 'Magento_Ui/js/form/element/textarea',
        'multiline' => 'Magento_Ui/js/form/components/group',
        'multiselect' => 'Magento_Ui/js/form/element/multiselect',
    ];

    /**
     * @var array
     */
    private $templateMap = [
        'image' => 'ui/form/element/media'
    ];

    /**
     * @var array
     */
    private $inputValidationMap = [
        'alpha' => 'validate-alpha',
        'numeric' => 'validate-number',
        'alphanumeric' => 'validate-alphanum',
        'url' => 'validate-url',
        'email' => 'email2',
    ];

    /**
     * @var array
     */
    private $initiallyHiddenFields = ['region'];

    /**
     * Merge additional address fields for given provider
     *
     * @param array $elements
     * @param string $providerName
     * @param string $dataScopePrefix
     * @param array $fieldRows
     * @return array
     */
    public function merge($elements, $providerName, $dataScopePrefix, array $fieldRows = [])
    {
        foreach ($elements as $attributeCode => $attributeConfig) {
            $additionalConfig = isset($fieldRows[$attributeCode]) ? $fieldRows[$attributeCode] : [];
            $additionalConfig['config']['additionalClasses'] = 'aw-rma__field';

            if ($this->isFieldVisible($attributeConfig, $additionalConfig)
                || $this->isFieldInitiallyHidden($attributeCode)
            ) {
                $fieldRows[$attributeCode] = $this->getFieldConfig(
                    $attributeCode,
                    $attributeConfig,
                    $additionalConfig,
                    $providerName,
                    $dataScopePrefix
                );
            }
        }
        return $fieldRows;
    }

    /**
     * Retrieve UI field configuration for given attribute
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param array $additionalConfig
     * @param string $providerName
     * @param string $dataScopePrefix
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getFieldConfig(
        $attributeCode,
        array $attributeConfig,
        array $additionalConfig,
        $providerName,
        $dataScopePrefix
    ) {
        if (isset($attributeConfig['validation']['input_validation'])) {
            $validationRule = $attributeConfig['validation']['input_validation'];
            $attributeConfig['validation'][$this->inputValidationMap[$validationRule]] = true;
            unset($attributeConfig['validation']['input_validation']);
        }

        if ($attributeConfig['formElement'] == 'multiline') {
            return $this->getMultilineFieldConfig(
                $attributeCode,
                $attributeConfig,
                $additionalConfig,
                $providerName,
                $dataScopePrefix
            );
        }

        $uiComponent = isset($this->formElementMap[$attributeConfig['formElement']])
            ? $this->formElementMap[$attributeConfig['formElement']]
            : 'Magento_Ui/js/form/element/abstract';
        $elementTemplate = isset($this->templateMap[$attributeConfig['formElement']])
            ? $this->templateMap[$attributeConfig['formElement']]
            : 'ui/form/element/' . $attributeConfig['formElement'];

        $element = [
            'component' => isset($additionalConfig['component']) ? $additionalConfig['component'] : $uiComponent,
            'config' => [
                'customScope' => $this->prepareCustomScope($dataScopePrefix, $attributeConfig),
                'customEntry' => isset($additionalConfig['config']['customEntry'])
                    ? $additionalConfig['config']['customEntry']
                    : null,
                'template' => 'ui/form/field',
                'elementTmpl' => isset($additionalConfig['config']['elementTmpl'])
                    ? $additionalConfig['config']['elementTmpl']
                    : $elementTemplate,
                'tooltip' => isset($additionalConfig['config']['tooltip'])
                    ? $additionalConfig['config']['tooltip']
                    : null
            ],
            'dataScope' => $this->prepareDataScope($dataScopePrefix, $attributeConfig, $attributeCode),
            'label' => $attributeConfig['label'],
            'provider' => $providerName,
            'sortOrder' => isset($additionalConfig['sortOrder'])
                ? $additionalConfig['sortOrder']
                : $attributeConfig['sortOrder'],
            'validation' => $this->mergeConfigurationNode('validation', $additionalConfig, $attributeConfig),
            'options' => isset($attributeConfig['options']) ? $attributeConfig['options'] : [],
            'filterBy' => isset($additionalConfig['filterBy']) ? $additionalConfig['filterBy'] : null,
            'customEntry' => isset($additionalConfig['customEntry']) ? $additionalConfig['customEntry'] : null
        ];

        if (isset($additionalConfig['visible'])) {
            $element['visible'] = $additionalConfig['visible'];
        } elseif ($this->isFieldInitiallyHidden($attributeCode)) {
            $element['visible'] = false;
        } else {
            $element['visible'] = true;
        }
        if (isset($attributeConfig['value']) && $attributeConfig['value'] != null) {
            $element['value'] = $attributeConfig['value'];
        } elseif (isset($attributeConfig['default']) && $attributeConfig['default'] != null) {
            $element['value'] = $attributeConfig['default'];
        }
        if (isset($additionalConfig['config']['additionalClasses'])) {
            $element['config']['additionalClasses'] = $additionalConfig['config']['additionalClasses'];
            $element['config']['customClasses'] = $additionalConfig['config']['additionalClasses'];
        }

        return $element;
    }

    /**
     * Merge two configuration nodes recursively
     *
     * @param string $nodeName
     * @param array $mainSource
     * @param array $additionalSource
     * @return array
     */
    private function mergeConfigurationNode($nodeName, array $mainSource, array $additionalSource)
    {
        $mainData = isset($mainSource[$nodeName]) ? $mainSource[$nodeName] : [];
        $additionalData = isset($additionalSource[$nodeName]) ? $additionalSource[$nodeName] : [];
        return array_replace_recursive($additionalData, $mainData);
    }

    /**
     * Check if address attribute is visible on frontend
     *
     * @param array $attributeConfig
     * @param array $additionalConfig
     * @return bool
     */
    private function isFieldVisible(array $attributeConfig, array $additionalConfig = [])
    {
        if ($attributeConfig['visible'] == false
            || (isset($additionalConfig['visible']) && $additionalConfig['visible'] == false)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check if field initially hidden
     *
     * @param string $attributeCode
     * @return bool
     */
    private function isFieldInitiallyHidden($attributeCode)
    {
        return in_array($attributeCode, $this->initiallyHiddenFields);
    }

    /**
     * Retrieve field configuration for street address attribute
     *
     * @param string $attributeCode
     * @param array $attributeConfig
     * @param array $additionalConfig
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getMultilineFieldConfig(
        $attributeCode,
        array $attributeConfig,
        array $additionalConfig,
        $providerName,
        $dataScopePrefix
    ) {
        $lines = [];
        unset($attributeConfig['validation']['required-entry']);
        for ($lineIndex = 0; $lineIndex < (int)$attributeConfig['size']; $lineIndex++) {
            $isFirstLine = $lineIndex === 0;
            $line = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => $this->prepareCustomScope($dataScopePrefix, $attributeConfig),
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input'
                ],
                'dataScope' => $lineIndex,
                'provider' => $providerName,
                'validation' => $isFirstLine
                    ? array_merge(
                        ['required-entry' => (bool)$attributeConfig['required']],
                        $attributeConfig['validation']
                    )
                    : $attributeConfig['validation']

            ];
            if ($isFirstLine && isset($attributeConfig['default']) && $attributeConfig['default'] != null) {
                $line['value'] = $attributeConfig['default'];
            }
            $lines[] = $line;
        }

        $additionalClasses[$attributeCode] = true;
        if (isset($additionalConfig['config']['additionalClasses'])) {
            $additionalClasses[$additionalConfig['config']['additionalClasses']] = true;
        }

        return [
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => $attributeConfig['label'],
            'required' => (bool)$attributeConfig['required'],
            'dataScope' => $this->prepareDataScope($dataScopePrefix, $attributeConfig, $attributeCode),
            'provider' => $providerName,
            'sortOrder' => $attributeConfig['sortOrder'],
            'type' => 'group',
            'config' => [
                'template' => 'ui/group/group',
                'additionalClasses' => $additionalClasses
            ],
            'children' => $lines,
        ];
    }

    /**
     * Prepare data scope
     *
     * @param string $dataScopePrefix
     * @param array $attributeConfig
     * @param string $attributeCode
     * @return string
     */
    private function prepareDataScope($dataScopePrefix, $attributeConfig, $attributeCode)
    {
        return $dataScopePrefix . ($attributeConfig['custom_attribute'] ? '.custom_attributes' : '')
            . '.' . $attributeCode;
    }

    /**
     * Prepare custom scope
     *
     * @param string $dataScopePrefix
     * @param array $attributeConfig
     * @return string
     */
    private function prepareCustomScope($dataScopePrefix, $attributeConfig)
    {
        return $dataScopePrefix . ($attributeConfig['custom_attribute'] ? '.custom_attributes' : '');
    }
}
