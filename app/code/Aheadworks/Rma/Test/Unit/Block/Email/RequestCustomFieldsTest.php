<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Block\Email;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Block\Email\RequestCustomFields;
use Aheadworks\Rma\Model\CustomField\Resolver\CustomField as CustomFieldResolver;

/**
 * Class RequestCustomFieldsTest
 * Test for \Aheadworks\Rma\Block\Email\RequestCustomFields
 *
 * @package Aheadworks\Rma\Test\Unit\Block\Email
 */
class RequestCustomFieldsTest extends TestCase
{
    /**
     * @var RequestCustomFields
     */
    private $block;

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
        $this->customFieldResolverMock = $this->getMockBuilder(CustomFieldResolver::class)
            ->setMethods(['getValue', 'getLabel'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $objectManager->getObject(
            RequestCustomFields::class,
            [
                'customFieldResolver' => $this->customFieldResolverMock
            ]
        );
    }

    /**
     * Test getCustomFields method, request is not set
     */
    public function testGetCustomFieldsRequestNotSet()
    {
        $this->assertTrue(empty($this->block->getCustomFields()));
    }

    /**
     * Test getRequestItems method, request items is not set
     */
    public function testGetCustomFieldsRequestItemsNotSet()
    {
        $data = new DataObject();
        $this->block->setRmaRequest($data);

        $this->assertTrue(empty($this->block->getCustomFields()));
    }

    /**
     * Test getCustomFields method
     */
    public function testGetCustomFields()
    {
        $customFieldValueMock = $this->getMockForAbstractClass(RequestCustomFieldValueInterface::class);
        $storeId = 1;
        $customFieldData = [
            'field_id' => 1,
            'value' => 1
        ];
        $expected = [
            'label' => 'value'
        ];

        $data = new DataObject(['custom_fields' => [$customFieldValueMock]]);
        $this->block->setRmaRequest($data)->setStoreId($storeId);

        $customFieldValueMock->expects($this->exactly(2))
            ->method('getFieldId')
            ->willReturn($customFieldData['field_id']);
        $customFieldValueMock->expects($this->once())
            ->method('getValue')
            ->willReturn($customFieldData['value']);

        $this->customFieldResolverMock->expects($this->once())
            ->method('getValue')
            ->with($customFieldData['field_id'], $customFieldData['value'], $storeId)
            ->willReturn('value');
        $this->customFieldResolverMock->expects($this->once())
            ->method('getLabel')
            ->with($customFieldData['field_id'], $storeId)
            ->willReturn('label');

        $this->assertEquals($expected, $this->block->getCustomFields());
    }
}
