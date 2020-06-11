<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Email;

use Aheadworks\Rma\Model\Email\Sender;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Email\EmailMetadataInterface;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Class SenderTest
 * Test for \Aheadworks\Rma\Model\Email\Sender
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Email
 */
class SenderTest extends TestCase
{
    /**
     * @var Sender
     */
    private $model;

    /**
     * @var TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->transportBuilderMock = $this->getMockBuilder(TransportBuilder::class)
            ->setMethods(
                [
                    'setTemplateIdentifier',
                    'setTemplateOptions',
                    'setTemplateVars',
                    'setFrom',
                    'addTo',
                    'getTransport',
                    'sendMessage'
                ]
            )->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Sender::class,
            [
                'transportBuilder' => $this->transportBuilderMock
            ]
        );
    }

    /**
     * Test send method
     */
    public function testSend()
    {
        $emailMetadata = [
            'template_id' => 1,
            'template_options' => ['option1', 'option2'],
            'template_variables' => ['var1', 'var2'],
            'sender_name' => 'roni',
            'sender_email' => 'roni@example.com',
            'recipient_name' => 'cost',
            'recipient_email' => 'cost@example.com'
        ];
        $emailMetadataMock = $this->getMockForAbstractClass(EmailMetadataInterface::class);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateId')
            ->willReturn($emailMetadata['template_id']);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateOptions')
            ->willReturn($emailMetadata['template_options']);
        $emailMetadataMock->expects($this->once())
            ->method('getTemplateVariables')
            ->willReturn($emailMetadata['template_variables']);
        $emailMetadataMock->expects($this->once())
            ->method('getSenderName')
            ->willReturn($emailMetadata['sender_name']);
        $emailMetadataMock->expects($this->once())
            ->method('getSenderEmail')
            ->willReturn($emailMetadata['sender_email']);
        $emailMetadataMock->expects($this->once())
            ->method('getRecipientName')
            ->willReturn($emailMetadata['recipient_name']);
        $emailMetadataMock->expects($this->once())
            ->method('getRecipientEmail')
            ->willReturn($emailMetadata['recipient_email']);

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($emailMetadata['template_id'])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with($emailMetadata['template_options'])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with($emailMetadata['template_variables'])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with(['name' => $emailMetadata['sender_name'], 'email' => $emailMetadata['sender_email']])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->with($emailMetadata['recipient_email'], $emailMetadata['recipient_name'])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('sendMessage')
            ->willReturnSelf();

        $this->model->send($emailMetadataMock);
    }
}
