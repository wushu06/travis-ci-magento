<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Rma\Model\StatusRepository;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\StatusFactory;
use Aheadworks\Rma\Model\Status as StatusModel;
use Aheadworks\Rma\Api\Data\StatusInterfaceFactory;
use Aheadworks\Rma\Api\Data\StatusSearchResultsInterface;
use Aheadworks\Rma\Api\Data\StatusSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Model\ResourceModel\Status\CollectionFactory as StatusCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Rma\Model\ResourceModel\Status\Collection as StatusCollection;
use Magento\Framework\Api\Filter;

/**
 * Class StatusRepositoryTest
 * Test for \Aheadworks\Rma\Model\StatusRepository
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatusRepositoryTest extends TestCase
{
    /**
     * @var StatusRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var StatusFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusFactoryMock;

    /**
     * @var StatusInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusDataFactoryMock;

    /**
     * @var StatusSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var StatusCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusCollectionFactoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var array
     */
    private $statusData = [
        'id' => 1,
        'name' => 'status 1'
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['load', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->statusFactoryMock = $this->getMockBuilder(StatusFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->statusDataFactoryMock = $this->getMockBuilder(StatusInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsFactoryMock = $this->getMockBuilder(StatusSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->setMethods(['buildOutputDataArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(JoinProcessorInterface::class);
        $this->statusCollectionFactoryMock = $this->getMockBuilder(StatusCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->model = $objectManager->getObject(
            StatusRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'statusFactory' => $this->statusFactoryMock,
                'statusDataFactory' => $this->statusDataFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'statusCollectionFactory' => $this->statusCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $storeId = 1;

        $statusModelMock = $this->getMockBuilder(StatusModel::class)
            ->setMethods(['setOrigData', 'getData', 'beforeSave'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->statusFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statusModelMock);

        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $statusMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->statusData['id']);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($statusModelMock, $this->statusData['id'], ['store_id' => $storeId]);
        $statusModelMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->statusData);
        $statusModelMock->expects($this->once())
            ->method('setOrigData')
            ->with(null, $this->statusData);

        $this->dataObjectProcessorMock->expects($this->at(0))
            ->method('buildOutputDataArray')
            ->with($statusMock, StatusInterface::class)
            ->willReturn($this->statusData);
        $this->dataObjectHelperMock->expects($this->at(0))
            ->method('populateWithArray')
            ->with($statusModelMock, $this->statusData, StatusInterface::class);

        $statusModelMock->expects($this->once())
            ->method('beforeSave');
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($statusModelMock);

        $statusMock2 = $this->getMockForAbstractClass(StatusInterface::class);
        $statusMock2->expects($this->once())
            ->method('getId')
            ->willReturn($this->statusData['id']);
        $this->statusDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statusMock2);
        $this->dataObjectProcessorMock->expects($this->at(1))
            ->method('buildOutputDataArray')
            ->with($statusModelMock, StatusInterface::class)
            ->willReturn($this->statusData);

        $this->assertSame($statusMock2, $this->model->save($statusMock));
    }

    /**
     * Testing of get method
     */
    public function testGet()
    {
        $storeId = 1;

        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $statusMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->statusData['id']);

        $this->statusDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statusMock);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($statusMock, $this->statusData['id'], ['store_id' => $storeId]);

        $this->assertSame($statusMock, $this->model->get($this->statusData['id']));
    }

    /**
     * Testing of get method, that proper exception is thrown if custom field not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with statusId = 1
     */
    public function testGetOnException()
    {
        $storeId = 1;

        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $statusMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->statusDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statusMock);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->assertSame($statusMock, $this->model->get($this->statusData['id']));
    }

    /**
     * Testing of getList method
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $storeId = 1;
        $filterName = 'Name';
        $filterValue = 'Sample Custom Field';
        $collectionSize = 5;
        $scCurrPage = 1;
        $scPageSize = 3;

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(StatusSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(StatusCollection::class)
            ->setMethods(
                ['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'setStoreId', 'getIterator']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->statusCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $statusModelMock = $this->getMockBuilder(StatusModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, StatusInterface::class);

        $filterGroupMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock]);
        $filterMock->expects($this->once())
            ->method('getConditionType')
            ->willReturn(false);
        $filterMock->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn($filterName);
        $filterMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($filterValue);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with($filterName, ['eq' => $filterValue]);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())
            ->method('getField')
            ->willReturn($filterName);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($filterName, SortOrder::SORT_ASC);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);
        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($scCurrPage);
        $collectionMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($scCurrPage)
            ->willReturnSelf();
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($scPageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($scPageSize)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$statusModelMock]));

        $statusMock = $this->getMockForAbstractClass(StatusInterface::class);
        $this->statusDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($statusMock);
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($statusModelMock, StatusInterface::class)
            ->willReturn($this->statusData);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($statusMock, $this->statusData, StatusInterface::class);

        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$statusMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }
}
