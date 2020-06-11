<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\CustomField;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\CustomField\CustomerEditAt;
use Aheadworks\Rma\Model\Source\CustomField\EditAt as EditAtStatusSource;

/**
 * Class CustomerEditAtTest
 * Test for \Aheadworks\Rma\Model\Source\CustomField\CustomerEditAt
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\CustomField
 */
class CustomerEditAtTest extends TestCase
{
    /**
     * @var CustomerEditAt
     */
    private $model;

    /**
     * @var EditAtStatusSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $editAtStatusSourceMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->editAtStatusSourceMock = $this->getMockBuilder(EditAtStatusSource::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            CustomerEditAt::class,
            [
                'editAtStatusSource' => $this->editAtStatusSourceMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $expected = [['label' => 'label', 'value' => 'value']];

        $this->editAtStatusSourceMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($expected);

        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
