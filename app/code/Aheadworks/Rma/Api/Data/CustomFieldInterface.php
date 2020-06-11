<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Custom field interface
 * @api
 */
interface CustomFieldInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const NAME = 'name';
    const IS_ACTIVE = 'is_active';
    const TYPE = 'type';
    const REFERS = 'refers';
    const WEBSITE_IDS = 'website_ids';
    const VISIBLE_FOR_STATUS_IDS = 'visible_for_status_ids';
    const EDITABLE_FOR_STATUS_IDS = 'editable_for_status_ids';
    const EDITABLE_ADMIN_FOR_STATUS_IDS = 'editable_admin_for_status_ids';
    const IS_REQUIRED = 'is_required';
    const IS_DISPLAY_IN_LABEL = 'is_display_in_label';
    const IS_INCLUDED_IN_REPORT = 'is_included_in_report';
    const OPTIONS = 'options';
    const FRONTEND_LABELS = 'frontend_labels';
    const STOREFRONT_LABEL = 'storefront_label';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive();

    /**
     * Set is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get refers
     *
     * @return string
     */
    public function getRefers();

    /**
     * Set refers
     *
     * @param string $refers
     * @return $this
     */
    public function setRefers($refers);

    /**
     * Get website ids
     *
     * @return int[]
     */
    public function getWebsiteIds();

    /**
     * Set website ids
     *
     * @param int[] $websiteIds
     * @return $this
     */
    public function setWebsiteIds($websiteIds);

    /**
     * Get visible for status ids
     *
     * @return int[]
     */
    public function getVisibleForStatusIds();

    /**
     * Set visible for status ids
     *
     * @param int[] $visibleForStatusIds
     * @return $this
     */
    public function setVisibleForStatusIds($visibleForStatusIds);

    /**
     * Get editable for status ids
     *
     * @return int[]
     */
    public function getEditableForStatusIds();

    /**
     * Set editable for status ids
     *
     * @param int[] $editableForStatusIds
     * @return $this
     */
    public function setEditableForStatusIds($editableForStatusIds);

    /**
     * Get editable admin for status ids
     *
     * @return int[]
     */
    public function getEditableAdminForStatusIds();

    /**
     * Set editable admin for status ids
     *
     * @param int[] $editableAdminForStatusIds
     * @return $this
     */
    public function setEditableAdminForStatusIds($editableAdminForStatusIds);

    /**
     * Is required
     *
     * @return bool
     */
    public function isRequired();

    /**
     * Set is required
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequired($isRequired);

    /**
     * Is display in label
     *
     * @return bool
     */
    public function isDisplayInLabel();

    /**
     * Set is display in label
     *
     * @param bool $isDisplayInLabel
     * @return $this
     */
    public function setIsDisplayInLabel($isDisplayInLabel);

    /**
     * Is included in exported report
     *
     * @return bool
     */
    public function isIncludedInReport();

    /**
     * Set is included in exported report
     *
     * @param bool $isIncludedInReport
     * @return $this
     */
    public function setIsIncludedInReport($isIncludedInReport);

    /**
     * Get options
     *
     * @return \Aheadworks\Rma\Api\Data\CustomFieldOptionInterface[]|null
     */
    public function getOptions();

    /**
     * Set options
     *
     * @param \Aheadworks\Rma\Api\Data\CustomFieldOptionInterface[] $options|null
     * @return $this
     */
    public function setOptions($options);

    /**
     * Get frontend labels
     *
     * @return \Aheadworks\Rma\Api\Data\StoreValueInterface[]
     */
    public function getFrontendLabels();

    /**
     * Set frontend labels
     *
     * @param \Aheadworks\Rma\Api\Data\StoreValueInterface[] $frontendLabels
     * @return $this
     */
    public function setFrontendLabels($frontendLabels);

    /**
     * Get storefront label
     *
     * @return string
     */
    public function getStorefrontLabel();

    /**
     * Set storefront label
     *
     * @param string $storefrontLabel
     * @return $this
     */
    public function setStorefrontLabel($storefrontLabel);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Rma\Api\Data\CustomFieldExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Rma\Api\Data\CustomFieldExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\CustomFieldExtensionInterface $extensionAttributes
    );
}
