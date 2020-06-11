<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Request\Order\ItemResolver;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Api\Data\OrderItemInterface;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\RequestItemList;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\Pool;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\DataObject;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\ItemResolverInterface;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\Finder as ItemFinder;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\ActionValidator;

/**
 * Class ReplacementTest
 * Test for \Aheadworks\Rma\Model\Request\Order\ItemResolver\RequestItemList
 *
 * @package AAheadworks\Rma\Test\Unit\Model\Request\Order\ItemResolver
 */
class RequestItemListTest extends TestCase
{
    /**
     * @var RequestItemList
     */
    private $model;

    /**
     * @var Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $poolMock;

    /**
     * @var ActionValidator
     */
    private $actionValidatorMock;

    /**
     * @var ItemFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemFinderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->poolMock = $this->createMock(Pool::class);
        $this->actionValidatorMock = $this->createMock(ActionValidator::class);
        $this->itemFinderMock = $this->createMock(ItemFinder::class);

        $this->model = $objectManager->getObject(
            RequestItemList::class,
            [
                'pool' => $this->poolMock,
                'actionValidator' => $this->actionValidatorMock,
                'itemFinder' => $this->itemFinderMock
            ]
        );
    }

    /**
     * Test getForReplacement method
     */
    public function testGetForReplacement()
    {
        $resultBuyRequest = [
            'id' => 2,
            'qty' => 3
        ];
        $requestMock = $this->createMock(RequestInterface::class);
        $orderMock =  $this->createMock(Order::class);
        $action = 'replace';
        $productType = 'simple';

        $orderItemMock1 = $this->getOrderItem(1, null, $productType);
        $orderItemMock2 = $this->getOrderItem(2, 1, $productType);
        $requestItemMock1 = $this->createMock(RequestItemInterface::class);
        $requestItemMock2 = $this->createMock(RequestItemInterface::class);
        $orderItemMock2->expects($this->once())
            ->method('getParentItem')
            ->willReturn($orderItemMock1);
        $this->itemFinderMock->expects($this->exactly(2))
            ->method('findOrderItem')
            ->withAnyParameters()
            ->willReturnOnConsecutiveCalls($orderItemMock1, $orderItemMock2);
        $this->actionValidatorMock->expects($this->exactly(2))
            ->method('isValidForRequestItem')
            ->withAnyParameters()
            ->willReturn('true');
        $requestMock->expects($this->once())
            ->method('getOrderItems')
            ->willReturn([$requestItemMock1, $requestItemMock2]);

        $requestItemMock1->expects($this->once())
            ->method('getItemId')
            ->willReturn(1);
        $requestItemMock2->expects($this->once())
            ->method('getItemId')
            ->willReturn(2);

        $itemResolver = $this->createMock(ItemResolverInterface::class);
        $this->poolMock->expects($this->exactly(2))
            ->method('getItemResolver')
            ->willReturn($itemResolver);
        $itemResolver->expects($this->exactly(2))
            ->method('resolveBuyRequest')
            ->withAnyParameters()
            ->willReturn($resultBuyRequest);

        $this->model->getForReplacement($requestMock, $orderMock, $action);
    }

    /**
     * Get order item
     *
     * @param int $id
     * @param int $parentId
     * @param string $type
     * @return \PHPUnit\Framework\MockObject\MockObject
     * @throws \ReflectionException
     */
    private function getOrderItem($id, $parentId, $type)
    {
        $buyRequestData = [
            'id' => $id,
            'qty' => 3
        ];
        $orderItemMock = $this->createMock(Item::class);
        $buyRequest = $this->createMock(DataObject::class);
        $orderItemMock->expects($this->any())
            ->method('getBuyRequest')
            ->willReturn($buyRequest);
        $buyRequest->expects($this->any())
            ->method('getData')
            ->willReturn($buyRequestData);
        $orderItemMock->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $orderItemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn($id);
        $orderItemMock->expects($this->any())
            ->method('getParentItemId')
            ->willReturn($parentId);
        $orderItemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn($type);

        return $orderItemMock;
    }
}
