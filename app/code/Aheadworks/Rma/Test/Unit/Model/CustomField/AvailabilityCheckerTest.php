<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\CustomField;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Model\Source\Request\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\CustomField\AvailabilityChecker;

/**
 * Class AvailabilityCheckerTest
 * Test for \Aheadworks\Rma\Model\CustomField\AvailabilityChecker
 *
 * @package Aheadworks\Rma\Test\Unit\Model\CustomField
 */
class AvailabilityCheckerTest extends TestCase
{
    /**
     * @var AvailabilityChecker
     */
    private $model;

    /**
     * @var CustomFieldRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customFieldRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->customFieldRepositoryMock = $this->getMockForAbstractClass(CustomFieldRepositoryInterface::class);
        $this->model = $objectManager->getObject(
            AvailabilityChecker::class,
            [
                'customFieldRepository' => $this->customFieldRepositoryMock
            ]
        );
    }

    /**
     * Test canVisibleByStatus method
     *
     * @param string $status
     * @param array $availableStatus
     * @param bool $expected
     * @dataProvider statusesDataProvider
     */
    public function testCanVisibleByStatus($status, $availableStatus, $expected)
    {
        $customFieldId = 1;

        $customFieldMock = $this->getMockForAbstractClass(CustomFieldInterface::class);
        $this->customFieldRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customFieldId)
            ->willReturn($customFieldMock);
        $customFieldMock->expects($this->once())
            ->method('getVisibleForStatusIds')
            ->willReturn($availableStatus);

        $this->assertEquals($expected, $this->model->canVisibleByStatus($customFieldId, $status));
    }

    /**
     * Test canEditableByStatus method
     *
     * @param string $status
     * @param array $availableStatus
     * @param bool $expected
     * @dataProvider statusesDataProvider
     */
    public function testCanEditableByStatus($status, $availableStatus, $expected)
    {
        $customFieldId = 1;

        $customFieldMock = $this->getMockForAbstractClass(CustomFieldInterface::class);
        $this->customFieldRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customFieldId)
            ->willReturn($customFieldMock);
        $customFieldMock->expects($this->once())
            ->method('getEditableForStatusIds')
            ->willReturn($availableStatus);

        $this->assertEquals($expected, $this->model->canEditableByStatus($customFieldId, $status));
    }

    /**
     * Test canEditableAdminByStatus method
     *
     * @param string $status
     * @param array $availableStatus
     * @param bool $expected
     * @dataProvider statusesDataProvider
     */
    public function testCanEditableAdminByStatus($status, $availableStatus, $expected)
    {
        $customFieldId = 1;

        $customFieldMock = $this->getMockForAbstractClass(CustomFieldInterface::class);
        $this->customFieldRepositoryMock->expects($this->once())
            ->method('get')
            ->with($customFieldId)
            ->willReturn($customFieldMock);
        $customFieldMock->expects($this->once())
            ->method('getEditableAdminForStatusIds')
            ->willReturn($availableStatus);

        $this->assertEquals($expected, $this->model->canEditableAdminByStatus($customFieldId, $status));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function statusesDataProvider()
    {
        return [
            [Status::APPROVED, [Status::PACKAGE_SENT], false],
            [Status::APPROVED, [Status::APPROVED], true]
        ];
    }
}
