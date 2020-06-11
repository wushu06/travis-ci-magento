<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Magento\Framework\Model\AbstractModel;
use Aheadworks\Rma\Model\ResourceModel\Status as ResourceStatus;

/**
 * Class Status
 *
 * @package Aheadworks\Rma\Model
 */
class Status extends AbstractModel implements StatusInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceStatus::class);
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
    public function isEmailCustomer()
    {
        return $this->getData(self::IS_EMAIL_CUSTOMER);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsEmailCustomer($isEmailCustomer)
    {
        return $this->setData(self::IS_EMAIL_CUSTOMER, $isEmailCustomer);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAdmin()
    {
        return $this->getData(self::IS_EMAIL_ADMIN);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsEmailAdmin($isEmailAdmin)
    {
        return $this->setData(self::IS_EMAIL_ADMIN, $isEmailAdmin);
    }

    /**
     * {@inheritdoc}
     */
    public function isThread()
    {
        return $this->getData(self::IS_THREAD);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsThread($isThread)
    {
        return $this->setData(self::IS_THREAD, $isThread);
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
    public function setStorefrontLabel($storefrontLabel)
    {
        return $this->setData(self::STOREFRONT_LABEL, $storefrontLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerTemplates()
    {
        return $this->getData(self::CUSTOMER_TEMPLATES);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerTemplates($customerTemplates)
    {
        return $this->setData(self::CUSTOMER_TEMPLATES, $customerTemplates);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorefrontCustomerTemplate()
    {
        return $this->getData(self::STOREFRONT_CUSTOMER_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStorefrontCustomerTemplate($storefrontCustomerTemplate)
    {
        return $this->setData(self::STOREFRONT_CUSTOMER_TEMPLATE, $storefrontCustomerTemplate);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminTemplates()
    {
        return $this->getData(self::ADMIN_TEMPLATES);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdminTemplates($adminTemplates)
    {
        return $this->setData(self::ADMIN_TEMPLATES, $adminTemplates);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorefrontAdminTemplate()
    {
        return $this->getData(self::STOREFRONT_ADMIN_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStorefrontAdminTemplate($storefrontAdminTemplate)
    {
        return $this->setData(self::STOREFRONT_ADMIN_TEMPLATE, $storefrontAdminTemplate);
    }

    /**
     * {@inheritdoc}
     */
    public function getThreadTemplates()
    {
        return $this->getData(self::THREAD_TEMPLATES);
    }

    /**
     * {@inheritdoc}
     */
    public function setThreadTemplates($threadTemplates)
    {
        return $this->setData(self::THREAD_TEMPLATES, $threadTemplates);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorefrontThreadTemplate()
    {
        return $this->getData(self::STOREFRONT_THREAD_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStorefrontThreadTemplate($storefrontThreadTemplate)
    {
        return $this->setData(self::STOREFRONT_THREAD_TEMPLATE, $storefrontThreadTemplate);
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
        \Aheadworks\Rma\Api\Data\StatusExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        if ($this->getId() && !empty($this->getOrigData(self::NAME))) {
            $this->setName($this->getOrigData(self::NAME));
        }
    }
}
