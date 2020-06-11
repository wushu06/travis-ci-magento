<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\ThreadMessage;

use Aheadworks\Rma\Model\Email\EmailMetadataInterface;
use Aheadworks\Rma\Model\Request\Email\Processor\AdminReply;
use Aheadworks\Rma\Model\Request\Email\Processor\CustomerReply;
use Aheadworks\Rma\Model\ThreadMessage\Notifier;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Email\Sender;
use Aheadworks\Rma\Model\Request\Email\ProcessorList;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class NotifierTest
 * Test for \Aheadworks\Rma\Model\ThreadMessage\Notifier
 *
 * @package Aheadworks\Rma\Test\Unit\Model\ThreadMessage
 */
class NotifierTest extends TestCase
{
    /**
     * @var Notifier
     */
    private $model;

    /**
     * @var Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $senderMock;

    /**
     * @var ProcessorList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailProcessorListMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->senderMock = $this->getMockBuilder(Sender::class)
            ->setMethods(['send'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailProcessorListMock = $this->getMockBuilder(ProcessorList::class)
            ->setMethods(['getProcessor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->model = $objectManager->getObject(
            Notifier::class,
            [
                'sender' => $this->senderMock,
                'emailProcessorList' => $this->emailProcessorListMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test notifyAboutNewMessage method
     *
     * @param bool $causedByAdmin
     * @dataProvider notifyAboutNewMessageDataProvider
     */
    public function testNotifyAboutNewMessage($causedByAdmin)
    {
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $emailMetadataMock = $this->getMockForAbstractClass(EmailMetadataInterface::class);

        if ($causedByAdmin) {
            $emailProcessor = ProcessorList::ADMIN_REPLY_PROCESSOR;
            $emailProcessorClass = AdminReply::class;
        } else {
            $emailProcessor = ProcessorList::CUSTOMER_REPLY_PROCESSOR;
            $emailProcessorClass = CustomerReply::class;
        }
        $emailProcessorMock = $this->getMockBuilder($emailProcessorClass)
            ->setMethods(['setRequest', 'setStoreId', 'process'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailProcessorListMock->expects($this->once())
            ->method('getProcessor')
            ->with($emailProcessor)
            ->willReturn($emailProcessorMock);
        $emailProcessorMock->expects($this->once())
            ->method('setRequest')
            ->with($requestMock)
            ->willReturnSelf();
        $emailProcessorMock->expects($this->once())
            ->method('setStoreId')
            ->with(null)
            ->willReturnSelf();
        $emailProcessorMock->expects($this->once())
            ->method('process')
            ->willReturn($emailMetadataMock);

        $this->senderMock->expects($this->once())
            ->method('send')
            ->with($emailMetadataMock)
            ->willReturn($emailMetadataMock);

        $this->assertTrue($this->model->notifyAboutNewMessage($requestMock, $causedByAdmin));
    }

    /**
     * Test notifyAboutNewMessage method on exception
     */
    public function testNotifyAboutNewMessageOnException()
    {
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $emailMetadataMock = $this->getMockForAbstractClass(EmailMetadataInterface::class);
        $causedByAdmin = true;
        $emailProcessor = ProcessorList::ADMIN_REPLY_PROCESSOR;
        $emailProcessorClass = AdminReply::class;
        $exceptionMessage = __('Exception message.');

        $emailProcessorMock = $this->getMockBuilder($emailProcessorClass)
            ->setMethods(['setRequest', 'setStoreId', 'process'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailProcessorListMock->expects($this->once())
            ->method('getProcessor')
            ->with($emailProcessor)
            ->willReturn($emailProcessorMock);
        $emailProcessorMock->expects($this->once())
            ->method('setRequest')
            ->with($requestMock)
            ->willReturnSelf();
        $emailProcessorMock->expects($this->once())
            ->method('setStoreId')
            ->with(null)
            ->willReturnSelf();
        $emailProcessorMock->expects($this->once())
            ->method('process')
            ->willReturn($emailMetadataMock);

        $this->senderMock->expects($this->once())
            ->method('send')
            ->with($emailMetadataMock)
            ->willThrowException(new MailException($exceptionMessage));
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->model->notifyAboutNewMessage($requestMock, $causedByAdmin));
    }

    /**
     * Data provider for notifyAboutNewMessage method test
     *
     * @return array
     */
    public function notifyAboutNewMessageDataProvider()
    {
        return [[true], [false]];
    }
}
