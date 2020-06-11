<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Block\Adminhtml\Status\Edit;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Block\Adminhtml\Status\Edit\Preview;
use Aheadworks\Rma\Model\Email\EmailMetadataInterface;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Mail\Template\FactoryInterface;
use Aheadworks\Rma\Model\Request\Email\Previewer;

/**
 * Class PreviewTest
 * Test for \Aheadworks\Rma\Block\Adminhtml\Status\Edit\Preview
 *
 * @package Aheadworks\Rma\Test\Unit\Block\Adminhtml\Status\Edit
 */
class PreviewTest extends TestCase
{
    /**
     * @var Preview
     */
    private $block;

    /**
     * @var Previewer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $previewerMock;

    /**
     * @var FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templateFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->previewerMock = $this->getMockBuilder(Previewer::class)
            ->setMethods(['preview'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFactoryMock = $this->getMockForAbstractClass(FactoryInterface::class);
        $this->block = $objectManager->getObject(
            Preview::class,
            [
                'previewer' => $this->previewerMock,
                'templateFactory' => $this->templateFactoryMock
            ]
        );
    }

    /**
     * Test getPreview method
     */
    public function testGetPreview()
    {
        $storeId = 1;
        $toAdmin = true;

        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $emailMetadataMock = $this->getMockForAbstractClass(EmailMetadataInterface::class);
        $this->previewerMock->expects($this->once())
            ->method('preview')
            ->with($storeId, $statusMock, $toAdmin)
            ->willReturn($emailMetadataMock);

        $this->block->setStoreId($storeId)->setStatus($statusMock)->setToAdmin($toAdmin);
        $this->assertSame($emailMetadataMock, $this->block->getPreview());
    }

    /**
     * Test getSubject method
     */
    public function testGetSubject()
    {
        $storeId = 1;
        $toAdmin = true;
        $templateVariables = ['var1', 'var2'];
        $templateOptions = ['opt1', 'opt2'];
        $expected = 'subject';
        $templateId = 1;

        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $emailMetadataMock = $this->getMockForAbstractClass(EmailMetadataInterface::class);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateVariables')
            ->willReturn($templateVariables);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateOptions')
            ->willReturn($templateOptions);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateId')
            ->willReturn($templateId);
        $this->previewerMock->expects($this->once())
            ->method('preview')
            ->with($storeId, $statusMock, $toAdmin)
            ->willReturn($emailMetadataMock);

        $templateMock = $this->getMockForAbstractClass(TemplateInterface::class);
        $this->templateFactoryMock->expects($this->once())
            ->method('get')
            ->with($templateId)
            ->willReturn($templateMock);
        $templateMock->expects($this->once())
            ->method('setVars')
            ->with($templateVariables)
            ->willReturnSelf();
        $templateMock->expects($this->once())
            ->method('setOptions')
            ->with($templateOptions)
            ->willReturnSelf();
        $templateMock->expects($this->once())
            ->method('processTemplate');
        $templateMock->expects($this->once())
            ->method('getSubject')
            ->willReturn($expected);

        $this->block->setStoreId($storeId)->setStatus($statusMock)->setToAdmin($toAdmin);
        $this->assertEquals($expected, $this->block->getSubject());
    }

    /**
     * Test getContent method
     */
    public function testGetContent()
    {
        $storeId = 1;
        $toAdmin = true;
        $templateVariables = ['var1', 'var2'];
        $templateOptions = ['opt1', 'opt2'];
        $expected = 'content';
        $templateId = 1;

        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $emailMetadataMock = $this->getMockForAbstractClass(EmailMetadataInterface::class);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateVariables')
            ->willReturn($templateVariables);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateOptions')
            ->willReturn($templateOptions);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateId')
            ->willReturn($templateId);
        $this->previewerMock->expects($this->once())
            ->method('preview')
            ->with($storeId, $statusMock, $toAdmin)
            ->willReturn($emailMetadataMock);

        $templateMock = $this->getMockForAbstractClass(TemplateInterface::class);
        $this->templateFactoryMock->expects($this->once())
            ->method('get')
            ->with($templateId)
            ->willReturn($templateMock);
        $templateMock->expects($this->once())
            ->method('setVars')
            ->with($templateVariables)
            ->willReturnSelf();
        $templateMock->expects($this->once())
            ->method('setOptions')
            ->with($templateOptions)
            ->willReturnSelf();
        $templateMock->expects($this->at(0))
            ->method('processTemplate');
        $templateMock->expects($this->at(3))
            ->method('processTemplate')
            ->willReturn($expected);

        $this->block->setStoreId($storeId)->setStatus($statusMock)->setToAdmin($toAdmin);
        $this->assertEquals($expected, $this->block->getContent());
    }
}
