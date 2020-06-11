<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Block\Email;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Block\Email\RequestItems;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;
use Aheadworks\Rma\Model\CustomField\Resolver\CustomField as CustomFieldResolver;

/**
 * Class RequestItemsTest
 * Test for \Aheadworks\Rma\Block\Email\RequestItems
 *
 * @package Aheadworks\Rma\Test\Unit\Block\Email
 */
class RequestLinkTest extends TestCase
{
    /**
     * @var RequestItems
     */
    private $block;

    /**
     * @var OrderItemResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemResolverMock;

    /**
     * @var CustomFieldResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customFieldResolverMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->orderItemResolverMock = $this->getMockBuilder(OrderItemResolver::class)
            ->setMethods(['getName', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customFieldResolverMock = $this->getMockBuilder(CustomFieldResolver::class)
            ->setMethods(['getValue', 'getLabel'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $objectManager->getObject(
            RequestItems::class,
            [
                'orderItemResolver' => $this->orderItemResolverMock,
                'customFieldResolver' => $this->customFieldResolverMock
            ]
        );
    }

    /**
     * Test getRequestItems method, request is not set
     */
    public function testGetRequestItemsRequestNotSet()
    {
        $this->assertTrue(empty($this->block->getRequestItems()));
    }

    /**
     * Test getRequestItems method, request items is not set
     */
    public function testGetRequestItemsRequestItemsNotSet()
    {
        $data = new DataObject();
        $this->block->setRmaRequest($data);

        $this->assertTrue(empty($this->block->getRequestItems()));
    }

    /**
     * Test getRequestItems method
     */
    public function testGetRequestItems()
    {
        $customFieldValueMock = $this->getMockForAbstractClass(RequestCustomFieldValueInterface::class);
        $requestItemMock = $this->getMockForAbstractClass(RequestItemInterface::class);
        $storeId = 1;
        $itemData = [
            'item_id' => 1,
            'qty' => 1,
            'custom_fields' => [
                [
                    'field_id' => 1,
                    'value' => 1
                ]
            ]
        ];
        $expected = [
            [
                'name' => 'name',
                'sku' => 'sku',
                'qty' => $itemData['qty'],
                'custom_fields' => [
                    'label' => 'value'
                ]
            ]
        ];

        $data = new DataObject(['items' => [$requestItemMock]]);
        $this->block->setRmaRequest($data)->setStoreId($storeId);

        $requestItemMock->expects($this->exactly(2))
            ->method('getItemId')
            ->willReturn($itemData['item_id']);
        $requestItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($itemData['qty']);
        $requestItemMock->expects($this->once())
            ->method('getCustomFields')
            ->willReturn([$customFieldValueMock]);

        $this->orderItemResolverMock->expects($this->once())
            ->method('getName')
            ->with($itemData['item_id'])
            ->willReturn($expected[0]['name']);
        $this->orderItemResolverMock->expects($this->once())
            ->method('getSku')
            ->with($itemData['item_id'])
            ->willReturn($expected[0]['sku']);

        $this->orderItemResolverMock->expects($this->once())
            ->method('getSku')
            ->with($itemData['item_id'])
            ->willReturn($expected[0]['sku']);

        $customFieldValueMock->expects($this->exactly(2))
            ->method('getFieldId')
            ->willReturn($itemData['custom_fields'][0]['field_id']);
        $customFieldValueMock->expects($this->once())
            ->method('getValue')
            ->willReturn($itemData['custom_fields'][0]['value']);

        $this->customFieldResolverMock->expects($this->once())
            ->method('getValue')
            ->with($itemData['custom_fields'][0]['field_id'], $itemData['custom_fields'][0]['value'], $storeId)
            ->willReturn('value');
        $this->customFieldResolverMock->expects($this->once())
            ->method('getLabel')
            ->with($itemData['custom_fields'][0]['field_id'], $storeId)
            ->willReturn('label');

        $this->assertEquals($expected, $this->block->getRequestItems());
    }
}
