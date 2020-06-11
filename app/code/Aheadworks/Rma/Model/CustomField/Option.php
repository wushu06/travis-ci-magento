<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField;

use Aheadworks\Rma\Api\Data\CustomFieldOptionInterface;
use Magento\Framework\Model\AbstractModel;
use Aheadworks\Rma\Model\ResourceModel\CustomField\Option as ResourceOption;

/**
 * Class Option
 *
 * @package Aheadworks\Rma\Model\CustomField
 */
class Option extends AbstractModel implements CustomFieldOptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(ResourceOption::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldId()
    {
        return $this->getData(self::FIELD_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldId($fieldId)
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * {@inheritdoc}
     */
    public function isDefault()
    {
        return $this->getData(self::IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDefault($isDefault)
    {
        return $this->setData(self::IS_DEFAULT, $isDefault);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->getData(self::ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        return $this->setData(self::ENABLED, $enabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabels()
    {
        return $this->getData(self::STORE_LABELS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreLabels($storeLabels)
    {
        return $this->setData(self::STORE_LABELS, $storeLabels);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorefrontLabel()
    {
        return $this->getData(self::STOREFRONT_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setStorefrontLabel($storefrontLabels)
    {
        return $this->setData(self::STOREFRONT_LABEL, $storefrontLabels);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionId()
    {
        return $this->getData(self::ACTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionId($actionId)
    {
        return $this->setData(self::ACTION_ID, $actionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionStatuses()
    {
        return $this->getData(self::ACTION_STATUSES);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionStatuses($actionStatuses)
    {
        return $this->setData(self::ACTION_STATUSES, $actionStatuses);
    }
}
