<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Rma\Model\CannedResponseRepository;
use Aheadworks\Rma\Api\Data\CannedResponseInterface;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\CannedResponseFactory;
use Aheadworks\Rma\Model\CannedResponse as CannedResponseModel;
use Aheadworks\Rma\Api\Data\CannedResponseInterfaceFactory;
use Aheadworks\Rma\Api\Data\CannedResponseSearchResultsInterface;
use Aheadworks\Rma\Api\Data\CannedResponseSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Model\ResourceModel\CannedResponse\CollectionFactory as CannedResponseCollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\CannedResponse\Collection as CannedResponseCollection;
use Magento\Framework\Api\Filter;

/**
 * Class CannedResponseRepositoryTest
 * Test for \Aheadworks\Rma\Model\CannedResponseRepository
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CannedResponseRepositoryTest extends TestCase
{
    /**
     * @var CannedResponseRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var CannedResponseFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cannedResponseFactoryMock;

    /**
     * @var CannedResponseInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cannedResponseDataFactoryMock;

    /**
     * @var CannedResponseSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
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
     * @var CannedResponseCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cannedResponseCollectionFactoryMock;

    /**
     * @var array
     */
    private $cannedResponseData = [
        'id' => 1,
        'title' => 'test response',
        'is_active' => true
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
            ->setMethods(['load', 'save', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cannedResponseFactoryMock = $this->getMockBuilder(CannedResponseFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cannedResponseDataFactoryMock = $this->getMockBuilder(CannedResponseInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsFactoryMock = $this->getMockBuilder(CannedResponseSearchResultsInterfaceFactory::class)
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
        $this->cannedResponseCollectionFactoryMock = $this->getMockBuilder(CannedResponseCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            CannedResponseRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'cannedResponseFactory' => $this->cannedResponseFactoryMock,
                'cannedResponseDataFactory' => $this->cannedResponseDataFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'cannedResponseCollectionFactory' => $this->cannedResponseCollectionFactoryMock
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $cannedResponseModelMock = $this->getMockBuilder(CannedResponseModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cannedResponseFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cannedResponseModelMock);

        $cannedResponseMock = $this->getMockForAbstractClass(CannedResponseInterface::class);
        $this->dataObjectProcessorMock->expects($this->at(0))
            ->method('buildOutputDataArray')
            ->with($cannedResponseMock, CannedResponseInterface::class)
            ->willReturn($this->cannedResponseData);
        $this->dataObjectHelperMock->expects($this->at(0))
            ->method('populateWithArray')
            ->with($cannedResponseModelMock, $this->cannedResponseData, CannedResponseInterface::class);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($cannedResponseModelMock);

        $cannedResponseMock2 = $this->getMockForAbstractClass(CannedResponseInterface::class);
        $cannedResponseMock2->expects($this->once())
            ->method('getId')
            ->willReturn($this->cannedResponseData['id']);
        $this->cannedResponseDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cannedResponseMock2);
        $this->dataObjectProcessorMock->expects($this->at(1))
            ->method('buildOutputDataArray')
            ->with($cannedResponseModelMock, CannedResponseInterface::class)
            ->willReturn($this->cannedResponseData);
        $this->dataObjectHelperMock->expects($this->at(1))
            ->method('populateWithArray')
            ->with($cannedResponseMock2, $this->cannedResponseData, CannedResponseInterface::class);

        $this->assertSame($cannedResponseMock2, $this->model->save($cannedResponseMock));
    }

    /**
     * Testing of get method
     */
    public function testGet()
    {
        $cannedResponseMock = $this->getMockForAbstractClass(CannedResponseInterface::class);
        $cannedResponseMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->cannedResponseData['id']);

        $this->cannedResponseDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cannedResponseMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($cannedResponseMock, $this->cannedResponseData['id']);

        $this->assertSame($cannedResponseMock, $this->model->get($this->cannedResponseData['id']));
    }

    /**
     * Testing of get method, that proper exception is thrown if canned response not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cannedResponseId = 1
     */
    public function testGetOnException()
    {
        $cannedResponseMock = $this->getMockForAbstractClass(CannedResponseInterface::class);
        $cannedResponseMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->cannedResponseDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cannedResponseMock);

        $this->assertSame($cannedResponseMock, $this->model->get($this->cannedResponseData['id']));
    }

    /**
     * Testing of getList method
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'title';
        $filterValue = 'some value';
        $collectionSize = 5;
        $scCurrPage = 1;
        $scPageSize = 3;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(CannedResponseSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(CannedResponseCollection::class)
            ->setMethods(
                ['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'getIterator']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->cannedResponseCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $cannedResponseModelMock = $this->getMockBuilder(CannedResponseModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, CannedResponseInterface::class);

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
            ->willReturn(new \ArrayIterator([$cannedResponseModelMock]));

        $cannedResponseMock = $this->getMockForAbstractClass(CannedResponseInterface::class);
        $this->cannedResponseDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cannedResponseMock);
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($cannedResponseModelMock, CannedResponseInterface::class)
            ->willReturn($this->cannedResponseData);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($cannedResponseMock, $this->cannedResponseData, CannedResponseInterface::class);

        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$cannedResponseMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test of delete instance
     */
    public function testDelete()
    {
        $cannedResponseMock = $this->getMockForAbstractClass(CannedResponseInterface::class);
        $cannedResponseMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($this->cannedResponseData['id']);

        $this->cannedResponseDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cannedResponseMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($cannedResponseMock, $this->cannedResponseData['id']);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($cannedResponseMock);

        $this->assertTrue($this->model->delete($cannedResponseMock));
    }

    /**
     * Test of delete instance by ID
     */
    public function testDeleteById()
    {
        $cannedResponseMock = $this->getMockForAbstractClass(CannedResponseInterface::class);
        $cannedResponseMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->cannedResponseData['id']);

        $this->cannedResponseDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($cannedResponseMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($cannedResponseMock, $this->cannedResponseData['id']);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($cannedResponseMock);
        $this->model->deleteById($this->cannedResponseData['id']);
    }
}
