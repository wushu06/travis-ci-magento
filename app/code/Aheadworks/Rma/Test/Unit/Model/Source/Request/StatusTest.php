<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\Request;

use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\Request\Status;
use Aheadworks\Rma\Model\Status\Request\StatusList;
use Aheadworks\Rma\Api\Data\StatusInterface;

/**
 * Class StatusTest
 * Test for \Aheadworks\Rma\Model\Source\Request\Status
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\Request
 */
class StatusTest extends TestCase
{
    /**
     * @var Status
     */
    private $model;

    /**
     * @var StatusList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusListMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->statusListMock = $this->createMock(StatusList::class);

        $testStatusData = [
            $this->createTestStatus(1, 'status1'),
            $this->createTestStatus(2, 'status2')
        ];
        $this->statusListMock->expects($this->once())
            ->method('retrieve')
            ->willReturn($testStatusData);

        $this->model = $objectManager->getObject(
            Status::class,
            [
                'statusList' => $this->statusListMock
            ]
        );
    }

    /**
     * Test getOptionsWithoutTranslation method
     */
    public function testGetOptionsWithoutTranslation()
    {
        $this->assertTrue(is_array($this->model->getOptionsWithoutTranslation()));
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->model->toOptionArray()));
    }

    /**
     * Test getOptionLabelByValue method on unknown status
     */
    public function testGetOptionLabelByValueOnUnknown()
    {
        $value = 'unknown';

        $this->assertEmpty($this->model->getOptionLabelByValue($value));
    }

    /**
     * Test getOptionLabelByValue method
     */
    public function testGetOptionLabelByValue()
    {
        $value = Status::APPROVED;

        $this->assertInstanceOf(Phrase::class, $this->model->getOptionLabelByValue($value));
    }

    /**
     * Create test status
     *
     * @param int $id
     * @param string $name
     * @return StatusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTestStatus($id, $name)
    {
        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $statusMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $statusMock->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        return $statusMock;
    }
}
