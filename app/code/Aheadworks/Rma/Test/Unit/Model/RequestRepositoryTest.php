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
use Aheadworks\Rma\Model\RequestRepository;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\RequestFactory;
use Aheadworks\Rma\Model\Request as RequestModel;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestSearchResultsInterface;
use Aheadworks\Rma\Api\Data\RequestSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory as RequestCollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\Request\Collection as RequestCollection;
use Magento\Framework\Api\Filter;

/**
 * Class RequestRepositoryTest
 * Test for \Aheadworks\Rma\Model\RequestRepository
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestRepositoryTest extends TestCase
{
    /**
     * @var RequestRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var RequestFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestFactoryMock;

    /**
     * @var RequestInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestDataFactoryMock;

    /**
     * @var RequestSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
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
     * @var RequestCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestCollectionFactoryMock;

    /**
     * @var array
     */
    private $requestData = [
        'id' => 1,
        'increment_id' => '000000004',
        'print_label' => []
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
        $this->requestFactoryMock = $this->getMockBuilder(RequestFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestDataFactoryMock = $this->getMockBuilder(RequestInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsFactoryMock = $this->getMockBuilder(RequestSearchResultsInterfaceFactory::class)
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
        $this->requestCollectionFactoryMock = $this->getMockBuilder(RequestCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            RequestRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'requestFactory' => $this->requestFactoryMock,
                'requestDataFactory' => $this->requestDataFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'requestCollectionFactory' => $this->requestCollectionFactoryMock
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $requestModelMock = $this->getMockBuilder(RequestModel::class)
            ->setMethods(['beforeSave', 'getPrintLabel'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestModelMock->expects($this->atLeastOnce())
            ->method('getPrintLabel')
            ->willReturn($this->requestData['print_label']);
        $this->requestFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($requestModelMock);

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->dataObjectProcessorMock->expects($this->at(0))
            ->method('buildOutputDataArray')
            ->with($requestMock, RequestInterface::class)
            ->willReturn($this->requestData);
        $this->dataObjectHelperMock->expects($this->at(0))
            ->method('populateWithArray')
            ->with($requestModelMock, $this->requestData, RequestInterface::class);

        $requestModelMock->expects($this->once())
            ->method('beforeSave');
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($requestModelMock);

        $requestMock2 = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock2->expects($this->once())
            ->method('getId')
            ->willReturn($this->requestData['id']);
        $this->requestDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($requestMock2);
        $this->dataObjectProcessorMock->expects($this->at(1))
            ->method('buildOutputDataArray')
            ->with($requestModelMock, RequestInterface::class)
            ->willReturn($this->requestData);
        $this->dataObjectHelperMock->expects($this->at(1))
            ->method('populateWithArray')
            ->with($requestMock2, $this->requestData, RequestInterface::class);

        $this->assertSame($requestMock2, $this->model->save($requestMock));
    }

    /**
     * Testing of get method
     */
    public function testGet()
    {
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->requestData['id']);

        $this->requestDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($requestMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($requestMock, $this->requestData['id']);

        $this->assertSame($requestMock, $this->model->get($this->requestData['id']));
    }

    /**
     * Testing of get method, that proper exception is thrown if custom field not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with requestId = 1
     */
    public function testGetOnException()
    {
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->requestDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($requestMock);

        $this->assertSame($requestMock, $this->model->get($this->requestData['id']));
    }

    /**
     * Testing of getList method
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterName = 'Name';
        $filterValue = 'Sample Custom Field';
        $collectionSize = 5;
        $scCurrPage = 1;
        $scPageSize = 3;

        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class, [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(RequestSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(RequestCollection::class)
            ->setMethods(
                ['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'getIterator']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $requestModelMock = $this->getMockBuilder(RequestModel::class)
            ->setMethods(['getPrintLabel'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestModelMock->expects($this->atLeastOnce())
            ->method('getPrintLabel')
            ->willReturn($this->requestData['print_label']);
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, RequestInterface::class);

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
            ->willReturn(new \ArrayIterator([$requestModelMock]));

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->requestDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($requestMock);
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($requestModelMock, RequestInterface::class)
            ->willReturn($this->requestData);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($requestMock, $this->requestData, RequestInterface::class);

        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$requestMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }
}
