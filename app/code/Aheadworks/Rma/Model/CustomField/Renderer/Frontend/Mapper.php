<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Renderer\Frontend;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Model\CustomField\AvailabilityChecker;
use Aheadworks\Rma\Model\Source\CustomField\EditAt;
use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class Mapper
 *
 * @package Aheadworks\Rma\Model\CustomField\Renderer\Frontend
 */
class Mapper
{
    /**
     * @var AvailabilityChecker
     */
    private $availabilityChecker;

    /**
     * @var array
     */
    private $elementClassMap = [
        Type::TEXT => ['input-text'],
        Type::SELECT => ['select'],
        Type::MULTI_SELECT => ['select', 'multiselect'],
        Type::TEXT_AREA => ['textarea']
    ];

    /**
     * @param AvailabilityChecker $availabilityChecker
     */
    public function __construct(
        AvailabilityChecker $availabilityChecker
    ) {
        $this->availabilityChecker = $availabilityChecker;
    }

    /**
     * Map custom field attribute
     *
     * @param CustomFieldInterface $customField
     * @param int $requestStatus
     * @param string $name
     * @return array
     */
    public function map($customField, $requestStatus, $name)
    {
        $result = [];
        if (count($customField->getOptions())) {
            $result = array_merge($result, $this->prepareOptions($customField, $requestStatus));
        }
        $result['uid'] = $this->getUid();
        $result['label'] = $customField->getStorefrontLabel();
        $result['is_visible'] = $this->isVisible($customField, $requestStatus);
        $result['is_editable'] = $this->isEditable($customField, $requestStatus);
        $result = array_merge($result, $this->getClasses($customField, $result['is_editable']));
        $result['field_name'] = $this->getFieldName($customField, $name);
        $result['custom_field_id'] = $customField->getId();

        return $result;
    }

    /**
     * Get validation rules data
     *
     * @param CustomFieldInterface $customField
     * @param bool $isEditable
     * @return array
     */
    private function getClasses($customField, $isEditable)
    {
        $fieldClass = $additionalClasses = [];
        if ($customField->isRequired() && $isEditable) {
            $fieldClass[] = 'required-entry';
            $additionalClasses[] = 'required';
        }
        $fieldClass = array_merge($fieldClass, $this->elementClassMap[$customField->getType()]);

        return ['field_class' => implode(' ', $fieldClass), 'additional_classes' => implode(' ', $additionalClasses)];
    }

    /**
     * Prepare options data
     *
     * @param CustomFieldInterface $customField
     * @param int $requestStatus
     * @return array
     */
    private function prepareOptions($customField, $requestStatus)
    {
        $optionsData = [];
        $defaultOptionsData = [];
        if (in_array($requestStatus, [EditAt::NEW_REQUEST_PAGE]) && $customField->getType() != Type::MULTI_SELECT) {
            $optionsData[] = ['value' => '', 'label' => __('Please select')];
        }
        foreach ($customField->getOptions() as $option) {
            if (!$option->getEnabled()) {
                continue;
            }
            $optionsData[] = [
                'value' => $option->getId(),
                'label' => $option->getStorefrontLabel()
            ];
            if ($option->isDefault()) {
                $defaultOptionsData[] = $option->getId();
            }
        }

        return ['options' => $optionsData, 'default_options' => $defaultOptionsData];
    }

    /**
     * Retrieve custom field unique id
     *
     * @return string
     */
    private function getUid()
    {
        return uniqid();
    }

    /**
     * Check is visible
     *
     * @param CustomFieldInterface $customField
     * @param int $requestStatus
     * @return bool
     */
    private function isVisible($customField, $requestStatus)
    {
        return $this->availabilityChecker->canVisibleByStatus($customField->getId(), $requestStatus)
            || $this->availabilityChecker->canEditableByStatus($customField->getId(), $requestStatus);
    }

    /**
     * Check is editable
     *
     * @param CustomFieldInterface $customField
     * @param int $requestStatus
     * @return bool
     */
    private function isEditable($customField, $requestStatus)
    {
        return $this->availabilityChecker->canEditableByStatus($customField->getId(), $requestStatus);
    }

    /**
     * Retrieve field name
     *
     * @param CustomFieldInterface $customField
     * @param string $name
     * @return string
     */
    private function getFieldName($customField, $name)
    {
        $result = $this->serializeName($name);
        if ($customField->getType() == Type::MULTI_SELECT) {
            $result .= '[]';
        }

        return $result;
    }

    /**
     * Converts the incoming string which consists
     * of a specified delimiters into a format commonly used in form elements
     *
     * @param $name
     * @return string
     */
    private function serializeName($name)
    {
        $name = explode('.', $name);
        $result = array_shift($name);

        foreach ($name as $part) {
            $result .= '[' . $part . ']';
        }

        return $result;
    }
}
