<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Service;

use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Model\Source\Request\Status;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Rma\Model\Service\RequestService;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Api\ThreadMessageManagementInterface;
use Aheadworks\Rma\Model\Request\Modifier;
use Aheadworks\Rma\Model\Request\Notifier as RequestNotifier;
use Magento\Framework\App\ResourceConnection;
use Aheadworks\Rma\Model\Url;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class RequestServiceTest
 * Test for \Aheadworks\Rma\Model\Service\RequestService
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 */
class RequestServiceTest extends TestCase
{
    /**
     * @var RequestService
     */
    private $model;

    /**
     * @var RequestRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestRepositoryMock;

    /**
     * @var Modifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modifierMock;

    /**
     * @var ThreadMessageManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $threadMessageManagementMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var RequestNotifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestNotifierMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestRepositoryMock = $this->getMockForAbstractClass(RequestRepositoryInterface::class);
        $this->threadMessageManagementMock = $this->getMockForAbstractClass(ThreadMessageManagementInterface::class);
        $this->modifierMock = $this->getMockBuilder(Modifier::class)
            ->setMethods(['modifyRequestBeforeCreate', 'modifyRequestBeforeUpdate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestNotifierMock = $this->getMockBuilder(RequestNotifier::class)
            ->setMethods(['notifyAboutStatusChangeOnThread', 'notifyAboutStatusChangeOnEmail'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock = $this->getMockBuilder(Url::class)
            ->setMethods(['getEncryptUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            RequestService::class,
            [
                'requestRepository' => $this->requestRepositoryMock,
                'modifier' => $this->modifierMock,
                'threadMessageManagement' => $this->threadMessageManagementMock,
                'resourceConnection' => $this->resourceConnectionMock,
                'requestNotifier' => $this->requestNotifierMock,
                'url' => $this->urlMock,
            ]
        );
    }

    /**
     * Test createRequest method
     *
     * @param ThreadMessageInterface|\PHPUnit_Framework_MockObject_MockObject $threadMessage
     * @dataProvider createRequestDataProvider
     */
    public function testCreateRequest($threadMessage)
    {
        $causedByAdmin = true;
        $storeId = 1;
        $customerId = 1;
        $requestId = 1;
        $lastReplyBy = Owner::CUSTOMER;

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->modifierMock->expects($this->once())
            ->method('modifyRequestBeforeCreate')
            ->with($requestMock, $causedByAdmin, $storeId)
            ->willReturn($requestMock);

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $connectionMock->expects($this->once())
            ->method('beginTransaction');
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $this->requestRepositoryMock->expects($this->once())
            ->method('save')
            ->with($requestMock)
            ->willReturn($requestMock);

        $requestMock->expects($this->atLeastOnce())
            ->method('getThreadMessage')
            ->willReturn($threadMessage);
        if (!empty($threadMessage)) {
            $requestMock->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn($requestId);
            $requestMock->expects($this->atLeastOnce())
                ->method('getCustomerId')
                ->willReturn($customerId);
            $requestMock->expects($this->atLeastOnce())
                ->method('getLastReplyBy')
                ->willReturn($lastReplyBy);

            $threadMessage->expects($this->at(0))
                ->method('setRequestId')
                ->with($requestId)
                ->willReturnSelf();
            $threadMessage->expects($this->at(1))
                ->method('setOwnerId')
                ->with($customerId)
                ->willReturnSelf();
            $threadMessage->expects($this->at(2))
                ->method('setOwnerType')
                ->with($lastReplyBy)
                ->willReturnSelf();
            $threadMessage->expects($this->at(3))
                ->method('setIsAuto')
                ->with(false)
                ->willReturnSelf();

            $this->threadMessageManagementMock->expects($this->once())
                ->method('addThreadMessage')
                ->with($threadMessage);
        }

        $connectionMock->expects($this->once())
            ->method('commit');

        $this->assertSame($requestMock, $this->model->createRequest($requestMock, $causedByAdmin, $storeId));
    }

    /**
     * Test updateRequest method
     *
     * @param int $newRequestStatusId
     * @param ThreadMessageInterface|\PHPUnit_Framework_MockObject_MockObject $threadMessage
     * @dataProvider updateRequestDataProvider
     */
    public function testUpdateRequest($newRequestStatusId, $threadMessage)
    {
        $causedByAdmin = true;
        $storeId = 1;
        $customerId = 1;
        $requestId = 1;
        $requestStatusId = Status::APPROVED;
        $lastReplyBy = Owner::CUSTOMER;

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($requestId);

        $oldRequestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->requestRepositoryMock->expects($this->once())
            ->method('get')
            ->with($requestId, true)
            ->willReturn($oldRequestMock);

        $oldRequestMock->expects($this->atLeastOnce())
            ->method('getStatusId')
            ->willReturn($requestStatusId);
        $requestMock->expects($this->atLeastOnce())
            ->method('getStatusId')
            ->willReturn($newRequestStatusId);

        $this->modifierMock->expects($this->once())
            ->method('modifyRequestBeforeUpdate')
            ->with($requestMock, $causedByAdmin)
            ->willReturn($requestMock);

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $connectionMock->expects($this->once())
            ->method('beginTransaction');
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $this->requestRepositoryMock->expects($this->once())
            ->method('save')
            ->with($requestMock)
            ->willReturn($requestMock);

        if ($requestStatusId != $newRequestStatusId) {
            $this->requestNotifierMock->expects($this->once())
                ->method('notifyAboutStatusChangeOnThread')
                ->with($requestMock, $storeId);
            $this->requestNotifierMock->expects($this->once())
                ->method('notifyAboutStatusChangeOnEmail')
                ->with($requestMock, $causedByAdmin, $storeId);
        }

        $requestMock->expects($this->atLeastOnce())
            ->method('getThreadMessage')
            ->willReturn($threadMessage);
        if (!empty($threadMessage)) {
            $requestMock->expects($this->atLeastOnce())
                ->method('getCustomerId')
                ->willReturn($customerId);
            $requestMock->expects($this->atLeastOnce())
                ->method('getLastReplyBy')
                ->willReturn($lastReplyBy);

            $threadMessage->expects($this->at(0))
                ->method('setRequestId')
                ->with($requestId)
                ->willReturnSelf();
            $threadMessage->expects($this->at(1))
                ->method('setOwnerId')
                ->with($customerId)
                ->willReturnSelf();
            $threadMessage->expects($this->at(2))
                ->method('setOwnerType')
                ->with($lastReplyBy)
                ->willReturnSelf();
            $threadMessage->expects($this->at(3))
                ->method('setIsAuto')
                ->with(false)
                ->willReturnSelf();

            $this->threadMessageManagementMock->expects($this->once())
                ->method('addThreadMessage')
                ->with($threadMessage);
        }

        $connectionMock->expects($this->once())
            ->method('commit');

        $this->assertSame($requestMock, $this->model->updateRequest($requestMock, $causedByAdmin, $storeId));
    }

    /**
     * Test changeStatus method
     *
     * @param bool $expectedValue
     * @param int $status
     * @dataProvider changeStatusDataProvider
     */
    public function testChangeStatus($expectedValue, $status)
    {
        $causedByAdmin = true;
        $storeId = 1;
        $requestStatusId = Status::APPROVED;
        $requestId = 1;

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->atLeastOnce())
            ->method('setStatusId')
            ->with($status);
        $requestMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($requestId);

        $oldRequestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->requestRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$requestId],
                [$requestId, true]
            )->willReturnOnConsecutiveCalls(
                $requestMock,
                $oldRequestMock
            );

        $oldRequestMock->expects($this->atLeastOnce())
            ->method('getStatusId')
            ->willReturn($requestStatusId);
        $requestMock->expects($this->atLeastOnce())
            ->method('getStatusId')
            ->willReturn($status);

        if ($requestStatusId != $status) {
            $this->modifierMock->expects($this->once())
                ->method('modifyRequestBeforeUpdate')
                ->with($requestMock, $causedByAdmin)
                ->willReturn($requestMock);

            $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
            $connectionMock->expects($this->once())
                ->method('beginTransaction');
            $this->resourceConnectionMock->expects($this->once())
                ->method('getConnection')
                ->willReturn($connectionMock);
            $this->requestRepositoryMock->expects($this->once())
                ->method('save')
                ->with($requestMock)
                ->willReturn($requestMock);

            $this->requestNotifierMock->expects($this->once())
                ->method('notifyAboutStatusChangeOnThread')
                ->with($requestMock, $storeId);
            $this->requestNotifierMock->expects($this->once())
                ->method('notifyAboutStatusChangeOnEmail')
                ->with($requestMock, $causedByAdmin, $storeId);

            $connectionMock->expects($this->once())
                ->method('commit');
        }

        $this->assertEquals(
            $expectedValue,
            $this->model->changeStatus($requestId, $status, $causedByAdmin, $storeId)
        );
    }

    /**
     * Test getPrintLabelUrl method
     */
    public function testGetPrintLabelUrl()
    {
        $storeId = 1;
        $requestId = 1;
        $externalLink = '59E290B959E74573EDE25';
        $expectedUrl = 'url';

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getExternalLink')
            ->willReturn($externalLink);
        $this->requestRepositoryMock->expects($this->once())
            ->method('get')
            ->with($requestId)
            ->willReturn($requestMock);
        $this->urlMock->expects($this->once())
            ->method('getEncryptUrl')
            ->with('aw_rma/request/printLabel', ['id' => $externalLink, 'store_id' => $storeId])
            ->willReturn($expectedUrl);

        $this->assertEquals($expectedUrl, $this->model->getPrintLabelUrl($requestId, $storeId));
    }

    /**
     * Data provider for createRequest test
     *
     * @return array
     */
    public function createRequestDataProvider()
    {
        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);

        return [[$threadMessageMock], [null]];
    }

    /**
     * Data provider for updateRequest test
     *
     * @return array
     */
    public function updateRequestDataProvider()
    {
        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);

        return [[Status::APPROVED, $threadMessageMock], [Status::PACKAGE_SENT, null]];
    }

    /**
     * Data provider for changeStatus test
     *
     * @return array
     */
    public function changeStatusDataProvider()
    {
        return [[false, Status::APPROVED]];
    }
}
