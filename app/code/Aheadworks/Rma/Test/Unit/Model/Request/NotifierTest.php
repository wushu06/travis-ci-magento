<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Request;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterfaceFactory;
use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Api\ThreadMessageManagementInterface;
use Aheadworks\Rma\Model\Email\EmailMetadataInterface;
use Aheadworks\Rma\Model\Request\Email\Processor\AdminChangedStatus;
use Aheadworks\Rma\Model\Request\Email\Processor\CustomerChangedStatus;
use Aheadworks\Rma\Model\Request\Notifier;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Email\Sender;
use Aheadworks\Rma\Model\Request\Email\ProcessorList;
use Aheadworks\Rma\Model\Source\Request\Status;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class NotifierTest
 * Test for \Aheadworks\Rma\Model\Request\Notifier
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Request
 */
class NotifierTest extends TestCase
{
    /**
     * @var Notifier
     */
    private $model;

    /**
     * @var  StatusRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusRepositoryMock;

    /**
     * @var ThreadMessageManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $threadMessageManagementMock;

    /**
     * @var ThreadMessageInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $threadMessageFactoryMock;

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
        $this->statusRepositoryMock = $this->getMockForAbstractClass(StatusRepositoryInterface::class);
        $this->threadMessageManagementMock = $this->getMockForAbstractClass(ThreadMessageManagementInterface::class);
        $this->threadMessageFactoryMock = $this->getMockBuilder(ThreadMessageInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
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
                'statusRepository' => $this->statusRepositoryMock,
                'threadMessageManagement' => $this->threadMessageManagementMock,
                'threadMessageFactory' => $this->threadMessageFactoryMock,
                'sender' => $this->senderMock,
                'emailProcessorList' => $this->emailProcessorListMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test notifyAboutStatusChangeOnThread method
     *
     * @param bool $isThread
     * @dataProvider notifyAboutStatusChangeOnThreadDataProvider
     */
    public function testNotifyAboutStatusChangeOnThread($isThread)
    {
        $storeId = null;
        $storefrontThreadTemplate = 'text';
        $requestId = 1;
        $statusId = Status::APPROVED;
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);

        $requestMock->expects($this->atLeastOnce())
            ->method('getStatusId')
            ->willReturn($statusId);
        $this->statusRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statusId, $storeId)
            ->willReturn($statusMock);
        $statusMock->expects($this->once())
            ->method('isThread')
            ->willReturn($isThread);

        if ($isThread) {
            $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);
            $this->threadMessageFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($threadMessageMock);
            $requestMock->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn($requestId);

            $statusMock->expects($this->once())
                ->method('getStorefrontThreadTemplate')
                ->willReturn($storefrontThreadTemplate);
            $threadMessageMock->expects($this->once())
                ->method('setText')
                ->with($storefrontThreadTemplate)
                ->willReturnSelf();
            $threadMessageMock->expects($this->once())
                ->method('setOwnerType')
                ->with(Owner::ADMIN)
                ->willReturnSelf();
            $threadMessageMock->expects($this->once())
                ->method('setOwnerId')
                ->with(0)
                ->willReturnSelf();
            $threadMessageMock->expects($this->once())
                ->method('setIsAuto')
                ->with(true)
                ->willReturnSelf();
            $threadMessageMock->expects($this->once())
                ->method('setRequestId')
                ->with($requestId)
                ->willReturnSelf();
        }

        $this->assertEquals($isThread, $this->model->notifyAboutStatusChangeOnThread($requestMock));
    }

    /**
     * Test notifyAboutStatusChangeOnEmail method
     */
    public function testNotifyAboutStatusChangeOnEmail()
    {
        $storeId = null;
        $statusId = Status::APPROVED;
        $isEmailCustomer = true;
        $isEmailAdmin = true;
        $causedByAdmin =  true;
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $emailMetadataMock = $this->getMockForAbstractClass(EmailMetadataInterface::class);
        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);

        $requestMock->expects($this->atLeastOnce())
            ->method('getStatusId')
            ->willReturn($statusId);
        $this->statusRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statusId, $storeId)
            ->willReturn($statusMock);
        $statusMock->expects($this->once())
            ->method('isEmailCustomer')
            ->willReturn($isEmailCustomer);
        $statusMock->expects($this->once())
            ->method('isEmailAdmin')
            ->willReturn($isEmailAdmin);

        $customerEmailProcessorMock = $this->getMockBuilder(CustomerChangedStatus::class)
            ->setMethods(['setStatus', 'setRequest', 'setStoreId', 'process'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerEmailProcessorMock->expects($this->once())
            ->method('setRequest')
            ->with($requestMock)
            ->willReturnSelf();
        $customerEmailProcessorMock->expects($this->once())
            ->method('setStatus')
            ->with($statusMock)
            ->willReturnSelf();
        $customerEmailProcessorMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $customerEmailProcessorMock->expects($this->once())
            ->method('process')
            ->willReturn($emailMetadataMock);

        $adminEmailProcessorMock = $this->getMockBuilder(AdminChangedStatus::class)
            ->setMethods(['setStatus', 'setRequest', 'setStoreId', 'process'])
            ->disableOriginalConstructor()
            ->getMock();
        $adminEmailProcessorMock->expects($this->once())
            ->method('setRequest')
            ->with($requestMock)
            ->willReturnSelf();
        $adminEmailProcessorMock->expects($this->once())
            ->method('setStatus')
            ->with($statusMock)
            ->willReturnSelf();
        $adminEmailProcessorMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $adminEmailProcessorMock->expects($this->once())
            ->method('process')
            ->willReturn($emailMetadataMock);

        $this->emailProcessorListMock->expects($this->exactly(2))
            ->method('getProcessor')
            ->withConsecutive(
                [ProcessorList::CUSTOMER_CHANGED_STATUS_PROCESSOR],
                [ProcessorList::ADMIN_CHANGED_STATUS_PROCESSOR]
            )->willReturnOnConsecutiveCalls(
                $customerEmailProcessorMock,
                $adminEmailProcessorMock
            );

        $this->senderMock->expects($this->exactly(2))
            ->method('send')
            ->with($emailMetadataMock)
            ->willReturn($emailMetadataMock);

        $this->assertTrue($this->model->notifyAboutStatusChangeOnEmail($requestMock, $causedByAdmin));
    }

    /**
     * Test notifyAboutStatusChangeOnEmail method on exception
     */
    public function testNotifyAboutStatusChangeOnEmailOnException()
    {
        $storeId = null;
        $statusId = Status::APPROVED;
        $isEmailCustomer = true;
        $causedByAdmin =  true;
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $emailMetadataMock = $this->getMockForAbstractClass(EmailMetadataInterface::class);
        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $exceptionMessage = __('Exception message.');

        $requestMock->expects($this->atLeastOnce())
            ->method('getStatusId')
            ->willReturn($statusId);
        $this->statusRepositoryMock->expects($this->once())
            ->method('get')
            ->with($statusId, $storeId)
            ->willReturn($statusMock);
        $statusMock->expects($this->once())
            ->method('isEmailCustomer')
            ->willReturn($isEmailCustomer);

        $customerEmailProcessorMock = $this->getMockBuilder(CustomerChangedStatus::class)
            ->setMethods(['setStatus', 'setRequest', 'setStoreId', 'process'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerEmailProcessorMock->expects($this->once())
            ->method('setRequest')
            ->with($requestMock)
            ->willReturnSelf();
        $customerEmailProcessorMock->expects($this->once())
            ->method('setStatus')
            ->with($statusMock)
            ->willReturnSelf();
        $customerEmailProcessorMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $customerEmailProcessorMock->expects($this->once())
            ->method('process')
            ->willReturn($emailMetadataMock);

        $this->emailProcessorListMock->expects($this->once())
            ->method('getProcessor')
            ->with(ProcessorList::CUSTOMER_CHANGED_STATUS_PROCESSOR)
            ->willReturn($customerEmailProcessorMock);

        $this->senderMock->expects($this->once())
            ->method('send')
            ->with($emailMetadataMock)
            ->willThrowException(new MailException($exceptionMessage));
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->model->notifyAboutStatusChangeOnEmail($requestMock, $causedByAdmin));
    }

    /**
     * Data provider for notifyAboutStatusChangeOnThread method test
     *
     * @return array
     */
    public function notifyAboutStatusChangeOnThreadDataProvider()
    {
        return [[true], [false]];
    }
}
