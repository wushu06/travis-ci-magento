<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Resolver;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionInterface;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomField
 *
 * @package Aheadworks\Rma\Model\CustomField\Resolver
 */
class CustomField
{
    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @param CustomFieldRepositoryInterface $customFieldRepository
     */
    public function __construct(
        CustomFieldRepositoryInterface $customFieldRepository
    ) {
        $this->customFieldRepository = $customFieldRepository;
    }

    /**
     * Retrieve custom field value
     *
     * @param int|null $customFieldId
     * @param string|array|null $customFieldValue
     * @param int|null $storeId
     * @return string
     */
    public function getValue($customFieldId, $customFieldValue, $storeId = null)
    {
        if (empty($customFieldId)) {
            return '';
        }

        $customField = $this->customFieldRepository->get($customFieldId, $storeId);
        if ($customField->getOptions()) {
            $value = $this->getLabelFromOptions(
                $customField->getOptions(),
                $customFieldValue
            );
        } else {
            $value = $customFieldValue;
        }

        return $value;
    }

    /**
     * Retrieve custom field label
     *
     * @param int|null $customFieldId
     * @param int|null $storeId
     * @return string
     */
    public function getLabel($customFieldId, $storeId = null)
    {
        if (empty($customFieldId)) {
            return '';
        }

        return $this->customFieldRepository->get($customFieldId, $storeId)->getStorefrontLabel();
    }

    /**
     * Retrieve custom field name
     *
     * @param int|null $customFieldId
     * @param int|null $storeId
     * @return string
     */
    public function getName($customFieldId, $storeId = null)
    {
        if (empty($customFieldId)) {
            return '';
        }

        return $this->customFieldRepository->get($customFieldId, $storeId)->getName();
    }

    /**
     * Check display on shipping label
     *
     * @param int|null $customFieldId
     * @param int|null $storeId
     * @return string
     */
    public function isDisplayOnShippingLabel($customFieldId, $storeId = null)
    {
        if (empty($customFieldId)) {
            return false;
        }

        return $this->customFieldRepository->get($customFieldId, $storeId)->isDisplayInLabel();
    }

    /**
     * Retrieve label from options
     *
     * @param CustomFieldOptionInterface[] $options
     * @param array|string $value
     * @return string
     */
    private function getLabelFromOptions($options, $value)
    {
        $labels = [];
        foreach ($options as $option) {
            if ((is_array($value) && in_array($option->getId(), $value))
                || (!is_array($value) && $value == $option->getId())
            ) {
                $labels[] = $option->getStorefrontLabel();
            }
        }

        return implode(', ', $labels);
    }
}
