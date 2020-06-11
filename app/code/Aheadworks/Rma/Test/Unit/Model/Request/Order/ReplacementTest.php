<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Request\Order;

use Aheadworks\Rma\Api\Data\CartInterface;
use Aheadworks\Rma\Model\Request\Order\Replacement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\Quote;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\RequestItemList;

/**
 * Class ReplacementTest
 * Test for \Aheadworks\Rma\Model\Request\Order\Replacement
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Request\Order
 */
class ReplacementTest extends TestCase
{
    /**
     * @var Replacement
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
     * @var Create|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCreateMock;

    /**
     * @var RequestItemList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestItemList;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestRepositoryMock = $this->createMock(RequestRepositoryInterface::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->orderCreateMock = $this->createMock(Create::class);
        $this->requestItemList = $this->createMock(RequestItemList::class);

        $this->model = $objectManager->getObject(
            Replacement::class,
            [
                'requestRepository' => $this->requestRepositoryMock,
                'orderRepository' => $this->orderRepositoryMock,
                'orderCreate' => $this->orderCreateMock,
                'requestItemList' => $this->requestItemList
            ]
        );
    }

    /**
     * Test prepare method
     */
    public function testPrepare()
    {
        $requestId = 3;
        $orderId = 10;
        $requestedItems = [
            10 => [15 => ['id' => 15, 'qty' => 2] ],
            20 => [25 => ['id' => 17, 'qty' => 3] ]
        ];

        $requestMock = $this->createMock(RequestInterface::class);
        $orderMock =  $this->createMock(Order::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['setAwRmaRequestId', 'removeAllItems'])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn($orderId);
        $quoteMock->expects($this->once())
            ->method('setAwRmaRequestId')
            ->with($requestId)
            ->willReturnSelf();

        $quoteMock->expects($this->once())
            ->method('removeAllItems')
            ->willReturnSelf();

        $this->requestRepositoryMock->expects($this->once())
            ->method('get')
            ->with($requestId)
            ->willReturn($requestMock);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);
        $this->orderCreateMock->expects($this->once())
            ->method('initFromOrder')
            ->with($orderMock)
            ->willReturnSelf();
        $this->orderCreateMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->orderCreateMock->expects($this->any())
            ->method('addProduct')
            ->withAnyParameters()
            ->willReturnSelf();
        $this->orderCreateMock->expects($this->any())
            ->method('saveQuote')
            ->willReturnSelf();

        $this->requestItemList->expects($this->once())
            ->method('getForReplacement')
            ->with($requestMock, $orderMock)
            ->willReturn($requestedItems);

        $this->model->prepare($requestId);
    }
}
