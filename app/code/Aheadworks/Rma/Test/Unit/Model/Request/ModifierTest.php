<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Request;

use Aheadworks\Rma\Api\Data\RequestPrintLabelInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Model\Request\Modifier;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Request\Update\Validator as UpdateValidator;
use Aheadworks\Rma\Model\Request\Update\ValidatorFactory as UpdateValidatorFactory;
use Aheadworks\Rma\Model\Request\Update\Merger;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aheadworks\Rma\Model\Source\Request\Status as RequestStatus;
use Aheadworks\Rma\Model\Request\PrintLabel\Resolver as PrintLabelResolver;

/**
 * Class IncrementIdGeneratorTest
 * Test for \Aheadworks\Rma\Model\Request\Modifier
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Request
 */
class ModifierTest extends TestCase
{
    /**
     * @var Modifier
     */
    private $model;

    /**
     * @var RequestRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestRepositoryMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var PrintLabelResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $printLabelResolverMock;

    /**
     * @var UpdateValidatorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $updateValidatorFactoryMock;

    /**
     * @var Merger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mergerMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestRepositoryMock = $this->getMockForAbstractClass(RequestRepositoryInterface::class);
        $this->orderRepositoryMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->printLabelResolverMock = $this->getMockBuilder(PrintLabelResolver::class)
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateValidatorFactoryMock = $this->getMockBuilder(UpdateValidatorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mergerMock = $this->getMockBuilder(Merger::class)
            ->setMethods(['mergeRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isAllowAutoApprove'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Modifier::class,
            [
                'requestRepository' => $this->requestRepositoryMock,
                'orderRepository' => $this->orderRepositoryMock,
                'printLabelResolver' => $this->printLabelResolverMock,
                'updateValidatorFactory' => $this->updateValidatorFactoryMock,
                'merger' => $this->mergerMock,
                'config' => $this->configMock
            ]
        );
    }

    /**
     * Test modifyRequestBeforeUpdate method
     */
    public function testModifyRequestBeforeUpdate()
    {
        $requestId = 1;
        $causedByAdmin = true;

        $newRequestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $newRequestMock->expects($this->once())
            ->method('getId')
            ->willReturn($requestId);
        $updateValidatorMock = $this->getMockBuilder(UpdateValidator::class)
            ->setMethods(['isValid', 'setIsCausedByAdmin', 'setRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->requestRepositoryMock->expects($this->once())
            ->method('get')
            ->with($requestId)
            ->willReturn($requestMock);

        $this->updateValidatorFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($updateValidatorMock);
        $updateValidatorMock->expects($this->once())
            ->method('setIsCausedByAdmin')
            ->with($causedByAdmin)
            ->willReturnSelf();
        $updateValidatorMock->expects($this->once())
            ->method('setRequest')
            ->with($requestMock)
            ->willReturnSelf();
        $updateValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($newRequestMock)
            ->willReturn(true);

        $this->mergerMock->expects($this->once())
            ->method('mergeRequest')
            ->with($requestMock, $newRequestMock);

        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);
        $requestMock->expects($this->once())
            ->method('getThreadMessage')
            ->willReturn($threadMessageMock);
        $requestMock->expects($this->once())
            ->method('setLastReplyBy')
            ->with(Owner::ADMIN);

        $this->assertEquals($requestMock, $this->model->modifyRequestBeforeUpdate($newRequestMock, $causedByAdmin));
    }

    /**
     * Test modifyRequestBeforeUpdate method on exception
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage RMA request cannot be changed. Message.
     */
    public function testModifyRequestBeforeUpdateOnException()
    {
        $requestId = 1;
        $causedByAdmin = true;
        $messages = ['Message.'];

        $newRequestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $newRequestMock->expects($this->once())
            ->method('getId')
            ->willReturn($requestId);
        $updateValidatorMock = $this->getMockBuilder(UpdateValidator::class)
            ->setMethods(['isValid', 'setIsCausedByAdmin', 'setRequest', 'getMessages'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->requestRepositoryMock->expects($this->once())
            ->method('get')
            ->with($requestId)
            ->willReturn($requestMock);

        $this->updateValidatorFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($updateValidatorMock);
        $updateValidatorMock->expects($this->once())
            ->method('setIsCausedByAdmin')
            ->with($causedByAdmin)
            ->willReturnSelf();
        $updateValidatorMock->expects($this->once())
            ->method('setRequest')
            ->with($requestMock)
            ->willReturnSelf();
        $updateValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($newRequestMock)
            ->willReturn(false);
        $updateValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $this->model->modifyRequestBeforeUpdate($newRequestMock, $causedByAdmin);
    }

    /**
     * Test modifyRequestBeforeCreate method
     */
    public function testModifyRequestBeforeCreate()
    {
        $storeId = 1;
        $orderId = 1;
        $paymentMethod = 'method name';
        $causedByAdmin = true;

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn($orderId);
        
        $this->configMock->expects($this->once())
            ->method('isAllowAutoApprove')
            ->willReturn(true);
                
        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $orderMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $orderPaymentMock = $this->getMockForAbstractClass(OrderPaymentInterface::class);
        $orderPaymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($paymentMethod);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($orderPaymentMock);
        $requestMock->expects($this->once())
            ->method('getThreadMessage')
            ->willReturn(null);
        $printLabelMock = $this->getMockForAbstractClass(RequestPrintLabelInterface::class);
        $this->printLabelResolverMock->expects($this->once())
            ->method('resolve')
            ->with($requestMock)
            ->willReturn($printLabelMock);

        $requestMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setPaymentMethod')
            ->with($paymentMethod)
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setStatusId')
            ->with(RequestStatus::APPROVED)
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setExternalLink')
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setLastReplyBy')
            ->with(0)
            ->willReturnSelf();
        $requestMock->expects($this->once())
            ->method('setPrintLabel')
            ->with($printLabelMock)
            ->willReturnSelf();

        $this->model->modifyRequestBeforeCreate($requestMock, $causedByAdmin, $storeId);
    }
}
