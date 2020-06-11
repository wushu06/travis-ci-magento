<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Aheadworks\Rma\Model
 */
class Config
{
    /**#@+
     * Constants for config path
     */
    const XML_PATH_GENERAL_RETURN_PERIOD = 'aw_rma/general/return_period';
    const XML_PATH_GENERAL_ALLOW_GUEST_REQUESTS = 'aw_rma/general/allow_guest_requests';
    const XML_PATH_GENERAL_CONFIRM_SHIPPING_POPUP_TEXT = 'aw_rma/general/confirm_shipping_popup_text';
    const XML_PATH_GENERAL_ALLOW_AUTO_APPROVE = 'aw_rma/general/allow_auto_approve';
    const XML_PATH_GENERAL_MANUFACTURER_ATTRIBUTE_CODE = 'aw_rma/general/manufacturer_attribute';
    const XML_PATH_BLOCKS_AND_POLICY_GUEST_PAGE_BLOCK = 'aw_rma/blocks_and_policy/guest_rma_block';
    const XML_PATH_BLOCKS_AND_POLICY_PRODUCT_SELECTION_BLOCK = 'aw_rma/blocks_and_policy/product_selection_block';
    const XML_PATH_BLOCKS_AND_POLICY_REASONS_AND_DETAILS_BLOCK = 'aw_rma/blocks_and_policy/reasons_and_details_block';
    const XML_PATH_BLOCKS_AND_POLICY_POLICY_BLOCK = 'aw_rma/blocks_and_policy/policy_block';
    const XML_PATH_CONTACTS_DEPARTMENT_NAME = 'aw_rma/contacts/department_name';
    const XML_PATH_CONTACTS_DEPARTMENT_EMAIL = 'aw_rma/contacts/department_email';
    const XML_PATH_CONTACTS_DEPARTMENT_ADDRESS = 'aw_rma/contacts/department_address';
    const XML_PATH_EMAIL_NOTIFICATION_REPLY_BY_ADMIN = 'aw_rma/email/template_to_customer_thread';
    const XML_PATH_EMAIL_NOTIFICATION_REPLY_BY_CUSTOMER = 'aw_rma/email/template_to_admin_thread';
    const XML_PATH_FILE_ATTACHMENTS_ALLOW_ATTACH_FILES = 'aw_rma/file_attachments/allow_attach_files';
    const XML_PATH_FILE_ATTACHMENTS_MAX_UPLOAD_FILE_SIZE = 'aw_rma/file_attachments/max_upload_file_size';
    const XML_PATH_FILE_ATTACHMENTS_ALLOW_FILE_EXTENSIONS = 'aw_rma/file_attachments/allow_file_extensions';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve return period
     *
     * @param int|null $storeId
     * @return int
     */
    public function getReturnPeriod($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_GENERAL_RETURN_PERIOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if allow guest create requests
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAllowGuestsCreateRequest($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_GENERAL_ALLOW_GUEST_REQUESTS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve "Confirm Shipping" alert text
     *
     * @param int|null $storeId
     * @return string
     */
    public function getConfirmShippingPopupText($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GENERAL_CONFIRM_SHIPPING_POPUP_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve guest RMA page block
     *
     * @param int|null $storeId
     * @return int
     */
    public function getGuestPageBlock($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_BLOCKS_AND_POLICY_GUEST_PAGE_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve reasons and details page block
     *
     * @param int|null $storeId
     * @return int
     */
    public function getReasonsAndDetailsBlock($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_BLOCKS_AND_POLICY_REASONS_AND_DETAILS_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve policy block
     *
     * @param int|null $storeId
     * @return int
     */
    public function getPolicyBlock($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_BLOCKS_AND_POLICY_POLICY_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve product selection block
     *
     * @param int|null $storeId
     * @return int
     */
    public function getProductSelectionBlock($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_BLOCKS_AND_POLICY_PRODUCT_SELECTION_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve RMA Department display name
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDepartmentDisplayName($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTACTS_DEPARTMENT_NAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve RMA Department email
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDepartmentEmail($storeId = null)
    {
        $departmentEmail = $this->scopeConfig->getValue(
            self::XML_PATH_CONTACTS_DEPARTMENT_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($departmentEmail)) {
            $departmentEmail = $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $departmentEmail;
    }

    /**
     * Retrieve RMA Department address
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDepartmentAddress($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTACTS_DEPARTMENT_ADDRESS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve Reply by Admin
     *
     * @param int|null $storeId
     * @return string|int
     */
    public function getEmailTemplateReplyByAdmin($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_NOTIFICATION_REPLY_BY_ADMIN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve RMA Department email
     *
     * @param int|null $storeId
     * @return string|int
     */
    public function getEmailTemplateReplyByCustomer($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_NOTIFICATION_REPLY_BY_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if allow customer to attach files
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAllowCustomerAttachFiles($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_FILE_ATTACHMENTS_ALLOW_ATTACH_FILES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve max upload file size
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMaxUploadFileSize($storeId = null)
    {
        $fileSizeMb = (int)$this->scopeConfig->getValue(
            self::XML_PATH_FILE_ATTACHMENTS_MAX_UPLOAD_FILE_SIZE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $fileSizeMb * 1024 * 1024;
    }

    /**
     * Retrieve allow file extensions
     *
     * @param int|null $storeId
     * @return array
     */
    public function getAllowFileExtensions($storeId = null)
    {
        $extensions = $this->scopeConfig->getValue(
            self::XML_PATH_FILE_ATTACHMENTS_ALLOW_FILE_EXTENSIONS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return empty($extensions) ? [] : explode(',', $extensions);
    }

    /**
     * Check if allow auto approve
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAllowAutoApprove($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_GENERAL_ALLOW_AUTO_APPROVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get manufacturer product attribute code
     *
     * @return int|null
     */
    public function getManufacturerProductAttributeCode()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_MANUFACTURER_ATTRIBUTE_CODE);
    }
}
