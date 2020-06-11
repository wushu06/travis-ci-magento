<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Block\Sales\Order;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderItemInterface;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Block\Sales\Order\RequestLink;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\UrlInterface;
use Aheadworks\Rma\Model\Request\Order as RequestOrder;
use Aheadworks\Rma\Model\Request\Order\Item as RequestOrderItem;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class RequestLinkTest
 * Test for \Aheadworks\Rma\Block\Sales\Order\RequestLink
 *
 * @package Aheadworks\Rma\Test\Unit\Block\Sales\Order
 */
class RequestLinkTest extends TestCase
{
    /**
     * @var RequestLink
     */
    private $block;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var RequestOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestOrderMock;

    /**
     * @var RequestOrderItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestOrderItemMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->orderRepositoryMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->requestOrderMock = $this->getMockBuilder(RequestOrder::class)
            ->setMethods(['isAllowedForOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestOrderItemMock = $this->getMockBuilder(RequestOrderItem::class)
            ->setMethods(['getOrderItemsToRequest', 'getItemMaxCount'])
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $objectManager->getObject(
            Context::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'request' => $this->requestMock
            ]
        );
        $this->block = $objectManager->getObject(
            RequestLink::class,
            [
                'context' => $contextMock,
                'orderRepository' => $this->orderRepositoryMock,
                'requestOrder' => $this->requestOrderMock,
                'requestOrderItem' => $this->requestOrderItemMock
            ]
        );
    }

    /**
     * Test canReturn method
     */
    public function testCanReturn()
    {
        $expected = true;
        $orderId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId);

        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $this->requestOrderMock->expects($this->once())
            ->method('isAllowedForOrder')
            ->with($orderMock)
            ->willReturn(true);

        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $orderItemMock = $this->getMockForAbstractClass(OrderItemInterface::class);
        $this->requestOrderItemMock->expects($this->once())
            ->method('getOrderItemsToRequest')
            ->with($orderId)
            ->willReturn([$orderItemMock]);
        $this->requestOrderItemMock->expects($this->once())
            ->method('getItemMaxCount')
            ->with($orderItemMock)
            ->willReturn(true);

        $this->assertEquals($expected, $this->block->canReturn());
    }

    /**
     * Test canReturn method, order is not exists
     */
    public function testCanReturnOrderNotExists()
    {
        $expected = false;
        $orderId = null;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willThrowException(new NoSuchEntityException(__('Requested entity doesn\'t exist')));

        $this->assertEquals($expected, $this->block->canReturn());
    }

    /**
     * Test canReturn method, order is not allowed
     */
    public function testCanReturnOrderNotAllowed()
    {
        $expected = false;
        $orderId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId);

        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $this->requestOrderMock->expects($this->once())
            ->method('isAllowedForOrder')
            ->with($orderMock)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->block->canReturn());
    }

    /**
     * Test canReturn method, order item is not allowed
     */
    public function testCanReturnItemNotAllowed()
    {
        $expected = false;
        $orderId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId);

        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $this->requestOrderMock->expects($this->once())
            ->method('isAllowedForOrder')
            ->with($orderMock)
            ->willReturn(true);

        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);
        $orderItemMock = $this->getMockForAbstractClass(OrderItemInterface::class);
        $this->requestOrderItemMock->expects($this->once())
            ->method('getOrderItemsToRequest')
            ->with($orderId)
            ->willReturn([$orderItemMock]);
        $this->requestOrderItemMock->expects($this->once())
            ->method('getItemMaxCount')
            ->with($orderItemMock)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->block->canReturn());
    }

    /**
     * Test getActionUrl method
     */
    public function testGetActionUrl()
    {
        $expected = 'url';
        $orderId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId);

        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($orderMock);

        $orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($orderId);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('aw_rma/customer/new', ['order_id' => $orderId])
            ->willReturn($expected);

        $this->assertEquals($expected, $this->block->getActionUrl());
    }
}
