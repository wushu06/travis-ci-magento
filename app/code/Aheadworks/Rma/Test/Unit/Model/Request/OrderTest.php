<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Request;

use Aheadworks\Rma\Model\Request\Order;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Aheadworks\Rma\Model\Config;
use Magento\Framework\Api\SortOrder;

/**
 * Class OrderTest
 * Test for \Aheadworks\Rma\Model\Request\Order
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Request
 */
class OrderTest extends TestCase
{
    /**
     * @var Order
     */
    private $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getReturnPeriod'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'addSortOrder', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock = $this->getMockBuilder(SortOrderBuilder::class)
            ->setMethods(['setField', 'setDirection', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Order::class,
            [
                'config' => $this->configMock,
                'orderRepository' => $this->orderRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'sortOrderBuilder' => $this->sortOrderBuilderMock
            ]
        );
    }

    /**
     * Test getOrders method
     *
     * @param int $returnPeriod
     * @dataProvider returnPeriodDataProvider
     */
    public function testGetOrders($returnPeriod)
    {
        $customerId = 1;
        $storeId = null;
        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sortOrderBuilderMock->expects($this->any())
            ->method('setField')
            ->with(OrderInterface::CREATED_AT)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->at(0))
            ->method('addFilter')
            ->with(OrderInterface::CUSTOMER_ID, $customerId)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->configMock->expects($this->once())
            ->method('getReturnPeriod')
            ->with($storeId)
            ->willReturn($returnPeriod);

        if ($returnPeriod > 0) {
            $this->searchCriteriaBuilderMock->expects($this->at(2))
                ->method('addFilter')
                ->willReturnSelf();
        }

        $orderSearchResultMock = $this->getMockForAbstractClass(OrderSearchResultInterface::class);
        $this->orderRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($orderSearchResultMock);

        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $orderSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderMock]);

        $this->assertEquals([$orderMock], $this->model->getOrders($customerId, $storeId));
    }

    /**
     * Test isAllowedForOrder method
     *
     * @param int $returnPeriod
     * @param bool $expected
     * @dataProvider returnPeriodDataProvider
     */
    public function testIsAllowedForOrder($returnPeriod, $expected)
    {
        $orderId = 1;
        $storeId = null;
        $orderState = \Magento\Sales\Model\Order::STATE_COMPLETE;
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getId', 'getState', 'getInvoiceCollection'])
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);
        $this->configMock->expects($this->once())
            ->method('getReturnPeriod')
            ->with($storeId)
            ->willReturn($returnPeriod);

        if ($returnPeriod) {
            $invoiceMock = $this->getMockBuilder(Invoice::class)
                ->setMethods(['getCreatedAt'])
                ->disableOriginalConstructor()
                ->getMock();
            $invoiceMock->expects($this->once())
                ->method('getCreatedAt')
                ->willReturn('2017-09-10 14:24:49');

            $orderMock->expects($this->once())
                ->method('getInvoiceCollection')
                ->willReturn([$invoiceMock]);
        }

        $this->assertEquals($expected, $this->model->isAllowedForOrder($orderMock, $storeId));
    }

    /**
     * Test isAllowedForOrder method, order doesn't complete
     */
    public function testIsAllowedForOrderNotComplete()
    {
        $expected = false;
        $orderId = 1;
        $storeId = null;
        $orderState = \Magento\Sales\Model\Order::STATE_CLOSED;
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getId', 'getState'])
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->assertEquals($expected, $this->model->isAllowedForOrder($orderMock, $storeId));
    }

    /**
     * Return period data provider
     *
     * @return array
     */
    public function returnPeriodDataProvider()
    {
        return [[30, false], [0, true]];
    }
}
