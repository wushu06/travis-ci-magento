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
use Aheadworks\Rma\Model\CustomFieldRepository;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Rma\Model\CustomFieldFactory;
use Aheadworks\Rma\Model\CustomField as CustomFieldModel;
use Aheadworks\Rma\Api\Data\CustomFieldInterfaceFactory;
use Aheadworks\Rma\Api\Data\CustomFieldSearchResultsInterface;
use Aheadworks\Rma\Api\Data\CustomFieldSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory as CustomFieldCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Rma\Model\ResourceModel\CustomField\Collection as CustomFieldCollection;
use Magento\Framework\Api\Filter;

/**
 * Class CustomFieldRepositoryTest
 * Test for \Aheadworks\Rma\Model\CustomFieldRepository
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomFieldRepositoryTest extends TestCase
{
    /**
     * @var CustomFieldRepository
     */
    private $model;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var CustomFieldFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customFieldFactoryMock;

    /**
     * @var CustomFieldInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customFieldDataFactoryMock;

    /**
     * @var CustomFieldSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
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
     * @var CustomFieldCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customFieldCollectionFactoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var array
     */
    private $customFieldData = [
        'id' => 1,
        'name' => 'custom field 1'
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
        $this->customFieldFactoryMock = $this->getMockBuilder(CustomFieldFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customFieldDataFactoryMock = $this->getMockBuilder(CustomFieldInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsFactoryMock = $this->getMockBuilder(CustomFieldSearchResultsInterfaceFactory::class)
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
        $this->customFieldCollectionFactoryMock = $this->getMockBuilder(CustomFieldCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->model = $objectManager->getObject(
            CustomFieldRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'customFieldFactory' => $this->customFieldFactoryMock,
                'customFieldDataFactory' => $this->customFieldDataFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'customFieldCollectionFactory' => $this->customFieldCollectionFactoryMock,
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

        $customFieldModelMock = $this->getMockBuilder(CustomFieldModel::class)
            ->setMethods(['setOrigData', 'getData', 'beforeSave'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customFieldFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customFieldModelMock);

        $customFieldMock = $this->getMockForAbstractClass(CustomFieldInterface::class);
        $customFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->customFieldData['id']);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($customFieldModelMock, $this->customFieldData['id'], ['store_id' => $storeId]);
        $customFieldModelMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->customFieldData);
        $customFieldModelMock->expects($this->once())
            ->method('setOrigData')
            ->with(null, $this->customFieldData);

        $this->dataObjectProcessorMock->expects($this->at(0))
            ->method('buildOutputDataArray')
            ->with($customFieldMock, CustomFieldInterface::class)
            ->willReturn($this->customFieldData);
        $this->dataObjectHelperMock->expects($this->at(0))
            ->method('populateWithArray')
            ->with($customFieldModelMock, $this->customFieldData, CustomFieldInterface::class);

        $customFieldModelMock->expects($this->once())
            ->method('beforeSave');
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($customFieldModelMock);

        $customFieldMock2 = $this->getMockForAbstractClass(CustomFieldInterface::class);
        $customFieldMock2->expects($this->once())
            ->method('getId')
            ->willReturn($this->customFieldData['id']);
        $this->customFieldDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customFieldMock2);
        $this->dataObjectProcessorMock->expects($this->at(1))
            ->method('buildOutputDataArray')
            ->with($customFieldModelMock, CustomFieldInterface::class)
            ->willReturn($this->customFieldData);

        $this->assertSame($customFieldMock2, $this->model->save($customFieldMock));
    }

    /**
     * Testing of get method
     */
    public function testGet()
    {
        $storeId = 1;

        $customFieldMock = $this->getMockForAbstractClass(CustomFieldInterface::class);
        $customFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->customFieldData['id']);

        $this->customFieldDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customFieldMock);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($customFieldMock, $this->customFieldData['id'], ['store_id' => $storeId]);

        $this->assertSame($customFieldMock, $this->model->get($this->customFieldData['id']));
    }

    /**
     * Testing of get method, that proper exception is thrown if custom field not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customFieldId = 1
     */
    public function testGetOnException()
    {
        $storeId = 1;

        $customFieldMock = $this->getMockForAbstractClass(CustomFieldInterface::class);
        $customFieldMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->customFieldDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customFieldMock);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->assertSame($customFieldMock, $this->model->get($this->customFieldData['id']));
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
        $searchResultsMock = $this->getMockForAbstractClass(CustomFieldSearchResultsInterface::class, [], '', false);
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->getMockBuilder(CustomFieldCollection::class)
            ->setMethods(
                ['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'setStoreId', 'getIterator']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->customFieldCollectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);
        $customFieldModelMock = $this->getMockBuilder(CustomFieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, CustomFieldInterface::class);

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
            ->willReturn(new \ArrayIterator([$customFieldModelMock]));

        $customFieldMock = $this->getMockForAbstractClass(CustomFieldInterface::class);
        $this->customFieldDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customFieldMock);
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($customFieldModelMock, CustomFieldInterface::class)
            ->willReturn($this->customFieldData);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($customFieldMock, $this->customFieldData, CustomFieldInterface::class);

        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$customFieldMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }
}
