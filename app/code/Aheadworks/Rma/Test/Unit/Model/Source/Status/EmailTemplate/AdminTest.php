<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\Status\EmailTemplate;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\Status\EmailTemplate\Admin;
use Magento\Config\Model\Config\Source\Email\Template as EmailTemplate;

/**
 * Class AdminTest
 * Test for \Aheadworks\Rma\Model\Source\Status\EmailTemplate\Admin
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\Status\EmailTemplate
 */
class AdminTest extends TestCase
{
    /**
     * @var Admin
     */
    private $model;

    /**
     * @var EmailTemplate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailTemplateMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->emailTemplateMock = $this->getMockBuilder(EmailTemplate::class)
            ->setMethods(['setPath', 'toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Admin::class,
            [
                'emailTemplate' => $this->emailTemplateMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $expected = [];

        $this->emailTemplateMock->expects($this->once())
            ->method('setPath')
            ->with('aw_rma_email_template_to_admin_status_changed')
            ->willReturnSelf();
        $this->emailTemplateMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($expected);

        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
