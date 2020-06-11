<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Status interface
 * @api
 */
interface StatusInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const NAME = 'name';
    const IS_ACTIVE = 'is_active';
    const SORT_ORDER = 'sort_order';
    const IS_EMAIL_CUSTOMER = 'is_email_customer';
    const IS_EMAIL_ADMIN = 'is_email_admin';
    const IS_THREAD = 'is_thread';
    const FRONTEND_LABELS = 'frontend_labels';
    const STOREFRONT_LABEL = 'storefront_label';
    const CUSTOMER_TEMPLATES = 'customer_templates';
    const STOREFRONT_CUSTOMER_TEMPLATE = 'storefront_customer_template';
    const ADMIN_TEMPLATES = 'admin_templates';
    const STOREFRONT_ADMIN_TEMPLATE = 'storefront_admin_template';
    const THREAD_TEMPLATES = 'thread_templates';
    const STOREFRONT_THREAD_TEMPLATE = 'storefront_thread_template';
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
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int|null $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Is email customer
     *
     * @return bool
     */
    public function isEmailCustomer();

    /**
     * Set is email customer
     *
     * @param bool $isEmailCustomer
     * @return $this
     */
    public function setIsEmailCustomer($isEmailCustomer);

    /**
     * Is email admin
     *
     * @return bool
     */
    public function isEmailAdmin();

    /**
     * Set is email admin
     *
     * @param bool $isEmailAdmin
     * @return $this
     */
    public function setIsEmailAdmin($isEmailAdmin);

    /**
     * Is thread
     *
     * @return bool
     */
    public function isThread();

    /**
     * Set is thread
     *
     * @param bool $isThread
     * @return $this
     */
    public function setIsThread($isThread);

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
     * Get customer templates
     *
     * @return \Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface[]
     */
    public function getCustomerTemplates();

    /**
     * Set customer templates
     *
     * @param \Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface[] $customerTemplates
     * @return $this
     */
    public function setCustomerTemplates($customerTemplates);

    /**
     * Get storefront customer template
     *
     * @return \Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface
     */
    public function getStorefrontCustomerTemplate();

    /**
     * Set storefront customer template
     *
     * @param \Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface $storefrontCustomerTemplate
     * @return $this
     */
    public function setStorefrontCustomerTemplate($storefrontCustomerTemplate);

    /**
     * Get admin templates
     *
     * @return \Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface[]
     */
    public function getAdminTemplates();

    /**
     * Set admin templates
     *
     * @param \Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface[] $adminTemplates
     * @return $this
     */
    public function setAdminTemplates($adminTemplates);

    /**
     * Get storefront admin template
     *
     * @return \Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface
     */
    public function getStorefrontAdminTemplate();

    /**
     * Set storefront admin template
     *
     * @param \Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface $storefrontAdminTemplate
     * @return $this
     */
    public function setStorefrontAdminTemplate($storefrontAdminTemplate);

    /**
     * Get thread templates
     *
     * @return \Aheadworks\Rma\Api\Data\StoreValueInterface[]
     */
    public function getThreadTemplates();

    /**
     * Set thread templates
     *
     * @param \Aheadworks\Rma\Api\Data\StoreValueInterface[] $threadTemplates
     * @return $this
     */
    public function setThreadTemplates($threadTemplates);

    /**
     * Get storefront thread template
     *
     * @return string
     */
    public function getStorefrontThreadTemplate();

    /**
     * Set storefront thread template
     *
     * @param string $storefrontThreadTemplate
     * @return $this
     */
    public function setStorefrontThreadTemplate($storefrontThreadTemplate);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Rma\Api\Data\StatusExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Rma\Api\Data\StatusExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\StatusExtensionInterface $extensionAttributes
    );
}
