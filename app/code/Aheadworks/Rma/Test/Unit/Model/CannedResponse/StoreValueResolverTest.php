<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\CannedResponse;

use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Model\StoreValue\ObjectResolver;
use Aheadworks\Rma\Model\CannedResponse\StoreValueResolver;
use Magento\Framework\Reflection\DataObjectProcessor;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;

/**
 * Class StoreValueResolverTest
 * @package Aheadworks\Rma\Test\Unit\Model\CannedResponse
 */
class StoreValueResolverTest extends TestCase
{
    /**
     * @var StoreValueResolver
     */
    private $model;

    /**
     * @var ObjectResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectResolverMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectResolverMock = $this->createPartialMock(ObjectResolver::class, ['resolve']);
        $this->dataObjectProcessorMock = $this->createPartialMock(DataObjectProcessor::class, ['buildOutputDataArray']);
        $this->model = $objectManager->getObject(
            StoreValueResolver::class,
            [
                'objectResolver' => $this->objectResolverMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock
            ]
        );
    }

    /**
     * Test getValueByStoreId method with set storeId
     */
    public function testGetValueForStoreWithSetStoreId()
    {
        $storeId = 1;
        $value0 = 'some value 1';
        $value1 = 'some value 2';
        $storeViewMock0 = $this->getMockForAbstractClass(StoreValueInterface::class);
        $storeViewMock0->expects($this->any())
            ->method('getStoreId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $storeViewMock0->expects($this->any())
            ->method('getValue')
            ->willReturn($value0);
        $storeViewMock1 = $this->getMockForAbstractClass(StoreValueInterface::class);
        $storeViewMock1->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $storeViewMock1->expects($this->any())
            ->method('getValue')
            ->willReturn($value1);
        $labelsData = [$storeViewMock0, $storeViewMock1];

        $this->objectResolverMock->expects($this->any())
            ->method('resolve')
            ->withConsecutive(
                [$storeViewMock0],
                [$storeViewMock1]
            )->willReturnOnConsecutiveCalls(
                $storeViewMock0,
                $storeViewMock1
            );

        $this->assertSame($value1, $this->model->getValueByStoreId($labelsData, $storeId));
    }

    /**
     * Test getLabelsForStore method with not exists storeId
     */
    public function testGetLabelsForStoreWithNotExistsStoreId()
    {
        $storeId = 1;
        $storeViewMock0 = $this->getDefaultStoreMock();
        $labelsData = [$storeViewMock0];

        $this->assertSame($storeViewMock0->getValue(), $this->model->getValueByStoreId($labelsData, $storeId));
    }

    /**
     * Test getLabelsForStore method without storeId
     */
    public function testGetLabelsForStoreWithoutStoreId()
    {
        $storeViewMock0 = $this->getDefaultStoreMock();
        $labelsData = [$storeViewMock0];

        $this->assertSame($storeViewMock0->getValue(), $this->model->getValueByStoreId($labelsData, null));
    }

    /**
     * Retrieve default store mock
     *
     * @return StoreValueInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDefaultStoreMock()
    {
        $value = 'some value';
        $storeViewMock0 = $this->getMockForAbstractClass(StoreValueInterface::class);
        $storeViewMock0->expects($this->any())
            ->method('getStoreId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $storeViewMock0->expects($this->any())
            ->method('getValue')
            ->willReturn($value);

        $this->objectResolverMock->expects($this->any())
            ->method('resolve')
            ->with($storeViewMock0)
            ->willReturn($storeViewMock0);

        return $storeViewMock0;
    }
}
