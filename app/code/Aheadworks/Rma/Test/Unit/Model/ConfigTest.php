<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model;

use Aheadworks\Rma\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigTest
 * Test for \Aheadworks\Rma\Model\Config
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->model = $objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Test getReturnPeriod method
     */
    public function testGetReturnPeriod()
    {
        $storeId = 1;
        $expected = 30;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_GENERAL_RETURN_PERIOD, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getReturnPeriod($storeId));
    }

    /**
     * Test isAllowGuestsCreateRequest method
     */
    public function testIsAllowGuestsCreateRequest()
    {
        $storeId = 1;
        $expected = true;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_GENERAL_ALLOW_GUEST_REQUESTS, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->isAllowGuestsCreateRequest($storeId));
    }

    /**
     * Test getConfirmShippingPopupText method
     */
    public function testGetConfirmShippingPopupText()
    {
        $storeId = 1;
        $expected = 'text';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_GENERAL_CONFIRM_SHIPPING_POPUP_TEXT, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getConfirmShippingPopupText($storeId));
    }

    /**
     * Test getGuestPageBlock method
     */
    public function testGetGuestPageBlock()
    {
        $storeId = 1;
        $expected = 1;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_BLOCKS_AND_POLICY_GUEST_PAGE_BLOCK, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getGuestPageBlock($storeId));
    }

    /**
     * Test getReasonsAndDetailsBlock method
     */
    public function testGetReasonsAndDetailsBlock()
    {
        $storeId = 1;
        $expected = 1;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_BLOCKS_AND_POLICY_REASONS_AND_DETAILS_BLOCK, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getReasonsAndDetailsBlock($storeId));
    }

    /**
     * Test getPolicyBlock method
     */
    public function testGetPolicyBlock()
    {
        $storeId = 1;
        $expected = 1;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_BLOCKS_AND_POLICY_POLICY_BLOCK, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getPolicyBlock($storeId));
    }

    /**
     * Test getProductSelectionBlock method
     */
    public function testGetProductSelectionBlock()
    {
        $storeId = 1;
        $expected = 1;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_BLOCKS_AND_POLICY_PRODUCT_SELECTION_BLOCK, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getProductSelectionBlock($storeId));
    }

    /**
     * Test getDepartmentDisplayName method
     */
    public function testGetDepartmentDisplayName()
    {
        $storeId = 1;
        $expected = 'department name';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_CONTACTS_DEPARTMENT_NAME, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getDepartmentDisplayName($storeId));
    }

    /**
     * Test getDepartmentEmail method
     */
    public function testGetDepartmentEmail()
    {
        $storeId = 1;
        $expected = 'department_email@gmail.com';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_CONTACTS_DEPARTMENT_EMAIL, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getDepartmentEmail($storeId));
    }

    /**
     * Test getDepartmentEmail method, the department email field is empty
     */
    public function testGetDepartmentEmailEmpty()
    {
        $storeId = 1;
        $expected = 'department_email@gmail.com';

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(Config::XML_PATH_CONTACTS_DEPARTMENT_EMAIL, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn(null);

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getDepartmentEmail($storeId));
    }

    /**
     * Test getDepartmentAddress method
     */
    public function testGetDepartmentAddress()
    {
        $storeId = 1;
        $expected = 'department_email@gmail.com';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_CONTACTS_DEPARTMENT_ADDRESS, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getDepartmentAddress($storeId));
    }

    /**
     * Test getEmailTemplateReplyByAdmin method
     */
    public function testGetEmailTemplateReplyByAdmin()
    {
        $storeId = 1;
        $expected = 'department_email@gmail.com';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_EMAIL_NOTIFICATION_REPLY_BY_ADMIN, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getEmailTemplateReplyByAdmin($storeId));
    }

    /**
     * Test getEmailTemplateReplyByCustomer method
     */
    public function testGetEmailTemplateReplyByCustomer()
    {
        $storeId = 1;
        $expected = 'department_email@gmail.com';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_EMAIL_NOTIFICATION_REPLY_BY_CUSTOMER, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getEmailTemplateReplyByCustomer($storeId));
    }

    /**
     * Test isAllowCustomerAttachFiles method
     */
    public function testIsAllowCustomerAttachFiles()
    {
        $storeId = 1;
        $expected = true;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_FILE_ATTACHMENTS_ALLOW_ATTACH_FILES, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->isAllowCustomerAttachFiles($storeId));
    }

    /**
     * Test getMaxUploadFileSize method
     */
    public function testGetMaxUploadFileSize()
    {
        $storeId = 1;
        $value = 2;
        $expected = 2097152;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_FILE_ATTACHMENTS_MAX_UPLOAD_FILE_SIZE, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);

        $this->assertEquals($expected, $this->model->getMaxUploadFileSize($storeId));
    }

    /**
     * Test getAllowFileExtensions method
     */
    public function testGetAllowFileExtensions()
    {
        $storeId = 1;
        $value = 'xml,pdf';
        $expected = ['xml', 'pdf'];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_FILE_ATTACHMENTS_ALLOW_FILE_EXTENSIONS, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);

        $this->assertEquals($expected, $this->model->getAllowFileExtensions($storeId));
    }

    /**
     * Test isAllowAutoApprove method
     */
    public function testIsAllowAutoApprove()
    {
        $storeId = 1;
        $expected = true;

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_GENERAL_ALLOW_AUTO_APPROVE, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->isAllowAutoApprove($storeId));
    }

    /**
     * Test getManufacturerProductAttributeCode method
     */
    public function testGetManufacturerProductAttributeCode()
    {
        $expected = 'manufacturer';

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_GENERAL_MANUFACTURER_ATTRIBUTE_CODE)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getManufacturerProductAttributeCode());
    }
}
