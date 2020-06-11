<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Magento\Framework\Model\AbstractModel;
use Aheadworks\Rma\Model\ResourceModel\CustomField as ResourceCustomField;
use Magento\Store\Model\Store;

/**
 * Class CustomField
 *
 * @package Aheadworks\Rma\Model
 */
class CustomField extends AbstractModel implements CustomFieldInterface
{
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceCustomField::class);
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
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getRefers()
    {
        return $this->getData(self::REFERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setRefers($refers)
    {
        return $this->setData(self::REFERS, $refers);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->getData(self::WEBSITE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setWebsiteIds($websiteIds)
    {
        return $this->setData(self::WEBSITE_IDS, $websiteIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleForStatusIds()
    {
        return $this->getData(self::VISIBLE_FOR_STATUS_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibleForStatusIds($visibleForStatusIds)
    {
        return $this->setData(self::VISIBLE_FOR_STATUS_IDS, $visibleForStatusIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableForStatusIds()
    {
        return $this->getData(self::EDITABLE_FOR_STATUS_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setEditableForStatusIds($editableForStatusIds)
    {
        return $this->setData(self::EDITABLE_FOR_STATUS_IDS, $editableForStatusIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableAdminForStatusIds()
    {
        return $this->getData(self::EDITABLE_ADMIN_FOR_STATUS_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setEditableAdminForStatusIds($editableAdminForStatusIds)
    {
        return $this->setData(self::EDITABLE_ADMIN_FOR_STATUS_IDS, $editableAdminForStatusIds);
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return $this->getData(self::IS_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsRequired($isRequired)
    {
        return $this->setData(self::IS_REQUIRED, $isRequired);
    }

    /**
     * {@inheritdoc}
     */
    public function isDisplayInLabel()
    {
        return $this->getData(self::IS_DISPLAY_IN_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDisplayInLabel($isDisplayInLabel)
    {
        return $this->setData(self::IS_DISPLAY_IN_LABEL, $isDisplayInLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function isIncludedInReport()
    {
        return $this->getData(self::IS_INCLUDED_IN_REPORT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsIncludedInReport($isIncludedInReport)
    {
        return $this->setData(self::IS_INCLUDED_IN_REPORT, $isIncludedInReport);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->getData(self::OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        return $this->setData(self::OPTIONS, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendLabels()
    {
        return $this->getData(self::FRONTEND_LABELS);
    }

    /**
     * {@inheritdoc}
     */
    public function setFrontendLabels($frontendLabels)
    {
        return $this->setData(self::FRONTEND_LABELS, $frontendLabels);
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
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\CustomFieldExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        if ($this->getId()) {
            $this->setType($this->getOrigData(self::TYPE));
            $this->setRefers($this->getOrigData(self::REFERS));
        }
    }
}
