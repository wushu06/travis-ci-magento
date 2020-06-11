<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\CustomField;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\CustomField\EditAt;
use Aheadworks\Rma\Model\Source\Request\Status as RequestStatusSource;

/**
 * Class EditAtTest
 * Test for \Aheadworks\Rma\Model\Source\CustomField\EditAt
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\CustomField
 */
class EditAtTest extends TestCase
{
    /**
     * @var EditAt
     */
    private $model;

    /**
     * @var RequestStatusSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestStatusSourceMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestStatusSourceMock = $this->getMockBuilder(RequestStatusSource::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            EditAt::class,
            [
                'requestStatusSource' => $this->requestStatusSourceMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $expected = [['label' => 'label', 'value' => 'value']];

        $this->requestStatusSourceMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($expected);

        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
