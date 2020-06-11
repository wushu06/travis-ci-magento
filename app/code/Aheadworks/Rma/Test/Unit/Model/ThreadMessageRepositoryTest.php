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
use Aheadworks\Rma\Model\ThreadMessageRepository;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\ThreadMessageFactory;
use Aheadworks\Rma\Model\ThreadMessage as ThreadMessageModel;
use Aheadworks\Rma\Api\Data\ThreadMessageInterfaceFactory;
use Aheadworks\Rma\Api\Data\ThreadMessageSearchResultsInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Model\ResourceModel\ThreadMessage\CollectionFactory as ThreadMessageCollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Collection as ThreadMessageCollection;
use Magento\Framework\Api\Filter;

/**
 * Class ThreadMessageRepositoryTest
 * Test for \Aheadworks\Rma\Model\ThreadMessageRepository
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThreadMessageRepositoryTest extends TestCase
{
    /**
     * @var ThreadMessageRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var ThreadMessageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $threadMessageFactoryMock;

    /**
     * @var ThreadMessageInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $threadMessageDataFactoryMock;

    /**
     * @var ThreadMessageSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
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
     * @var ThreadMessageCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $threadMessageCollectionFactoryMock;

    /**
     * @var array
     */
    private $threadMessageData = [
        'id' => 1,
        'text' => 'message text'
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
        $this->threadMessageFactoryMock = $this->getMockBuilder(ThreadMessageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->threadMessageDataFactoryMock = $this->getMockBuilder(ThreadMessageInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsFactoryMock = $this->getMockBuilder(ThreadMessageSearchResultsInterfaceFactory::class)
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
        $this->threadMessageCollectionFactoryMock = $this->getMockBuilder(ThreadMessageCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            ThreadMessageRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'threadMessageFactory' => $this->threadMessageFactoryMock,
                'threadMessageDataFactory' => $this->threadMessageDataFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'threadMessageCollectionFactory' => $this->threadMessageCollectionFactoryMock
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $threadMessageModelMock = $this->getMockBuilder(ThreadMessageModel::class)
            ->setMethods(['beforeSave'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->threadMessageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($threadMessageModelMock);

        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);
        $this->dataObjectProcessorMock->expects($this->at(0))
            ->method('buildOutputDataArray')
            ->with($threadMessageMock, ThreadMessageInterface::class)
            ->willReturn($this->threadMessageData);
        $this->dataObjectHelperMock->expects($this->at(0))
            ->method('populateWithArray')
            ->with($threadMessageModelMock, $this->threadMessageData, ThreadMessageInterface::class);

        $threadMessageModelMock->expects($this->once())
            ->method('beforeSave');
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($threadMessageModelMock);

        $threadMessageMock2 = $this->getMockForAbstractClass(ThreadMessageInterface::class);
        $threadMessageMock2->expects($this->once())
            ->method('getId')
            ->willReturn($this->threadMessageData['id']);
        $this->threadMessageDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($threadMessageMock2);
        $this->dataObjectProcessorMock->expects($this->at(1))
            ->method('buildOutputDataArray')
            ->with($threadMessageModelMock, ThreadMessageInterface::class)
            ->willReturn($this->threadMessageData);
        $this->dataObjectHelperMock->expects($this->at(1))
            ->method('populateWithArray')
            ->with($threadMessageMock2, $this->threadMessageData, ThreadMessageInterface::class);

        $this->assertSame($threadMessageMock2, $this->model->save($threadMessageMock));
    }

    /**
     * Testing of get method
     */
    public function testGet()
    {
        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);
        $threadMessageMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->threadMessageData['id']);

        $this->threadMessageDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($threadMessageMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($threadMessageMock, $this->threadMessageData['id']);

        $this->assertSame($threadMessageMock, $this->model->get($this->threadMessageData['id']));
    }

    /**
     * Testing of get method, that proper exception is thrown if custom field not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with threadMessageId = 1
     */
    public function testGetOnException()
    {
        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);
        $threadMessageMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->threadMessageDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($threadMessageMock);

        $this->assertSame($threadMessageMock, $this->model->get($this->threadMessageData['id']));
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
        $searchResultsMock = $this->getMockForAbstractClass(ThreadMessageSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(ThreadMessageCollection::class)
            ->setMethods(
                ['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'getIterator']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->threadMessageCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $threadMessageModelMock = $this->getMockBuilder(ThreadMessageModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, ThreadMessageInterface::class);

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
            ->willReturn(new \ArrayIterator([$threadMessageModelMock]));

        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);
        $this->threadMessageDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($threadMessageMock);
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($threadMessageModelMock, ThreadMessageInterface::class)
            ->willReturn($this->threadMessageData);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($threadMessageMock, $this->threadMessageData, ThreadMessageInterface::class);

        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$threadMessageMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }
}
