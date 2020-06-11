<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Status;

use Aheadworks\Rma\Model\Source\Request\Status;
use Aheadworks\Rma\Model\Status\RestrictionsInterface;
use Aheadworks\Rma\Model\Status\RestrictionsInterfaceFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Status\RestrictionsPool;
use Aheadworks\Rma\Model\Status\Request\StatusList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Status\Restrictions\CustomField as CustomFieldRestrictions;

/**
 * Class RestrictionsPoolTest
 * Test for \Aheadworks\Rma\Model\Status\RestrictionsPool
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Status
 */
class RestrictionsPoolTest extends TestCase
{
    /**
     * @var RestrictionsPool
     */
    private $model;

    /**
     * @var RestrictionsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restrictionsFactoryMock;

    /**
     * @var StatusList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusListMock;

    /**
     * @var CustomFieldRestrictions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customFieldRestrictionsMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->statusListMock = $this->createMock(StatusList::class);
        $this->customFieldRestrictionsMock = $this->createMock(CustomFieldRestrictions::class);
        $this->restrictionsFactoryMock = $this->getMockBuilder(RestrictionsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            RestrictionsPool::class,
            [
                'restrictionsFactory' => $this->restrictionsFactoryMock,
                'statusList' => $this->statusListMock,
                'customFieldRestrictions' => $this->customFieldRestrictionsMock,
                'customerRestrictions' => [
                    Status::APPROVED => []
                ],
                'adminRestrictions' => [
                    Status::APPROVED => []
                ]
            ]
        );
    }

    /**
     * Test getRestrictions method
     *
     * @param bool $isAdmin
     * @dataProvider getRestrictionsDataProvider
     */
    public function testGetRestrictions($isAdmin)
    {
        $status = Status::APPROVED;
        $restrictionsMock = $this->getMockForAbstractClass(RestrictionsInterface::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $this->restrictionsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($restrictionsMock);

        $this->assertSame($restrictionsMock, $this->model->getRestrictions($status, $requestMock, $isAdmin));
    }

    /**
     * Test getRestrictions method on exception
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown status: unknown requested
     */
    public function testGetRestrictionsOnException()
    {
        $status = 'unknown';
        $isAdmin = true;

        $requestMock = $this->createMock(RequestInterface::class);
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaMock->expects($this->once())
            ->method('addFilter')
            ->with(StatusInterface::ID, $status)
            ->willReturnSelf();

        $this->statusListMock->expects($this->once())
            ->method('getSearchCriteriaBuilder')
            ->willReturn($searchCriteriaMock);
        $this->statusListMock->expects($this->once())
            ->method('retrieve')
            ->willReturn([]);

        $this->model->getRestrictions($status, $requestMock, $isAdmin);
    }

    /**
     * Data provider for getRestrictions method test
     *
     * @return array
     */
    public function getRestrictionsDataProvider()
    {
        return [[true], [false]];
    }
}
