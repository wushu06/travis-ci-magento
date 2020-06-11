<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\StoreValue;

use Aheadworks\Rma\Model\StoreValue\ObjectResolver;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class ObjectResolverTest
 * @package Aheadworks\Rma\Test\Unit\StoreValue
 */
class ObjectResolverTest extends TestCase
{
    /**
     * @var ObjectResolver
     */
    private $model;

    /**
     * @var StoreValueInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeValueFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->storeValueFactoryMock = $this->createPartialMock(
            StoreValueInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->createPartialMock(
            DataObjectHelper::class,
            ['populateWithArray']
        );
        $this->model = $objectManager->getObject(
            ObjectResolver::class,
            [
                'storeValueFactory' => $this->storeValueFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock
            ]
        );
    }

    /**
     * Test resolve method
     *
     * @param array|StoreValueInterface $storeValueMock
     * @param StoreValueInterface $expected
     * @dataProvider resolveDataProvider
     */
    public function testResolve($storeValueMock, $expected)
    {
        if (is_array($storeValueMock)) {
            $this->storeValueFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($expected);
            $this->dataObjectHelperMock->expects($this->once())
                ->method('populateWithArray')
                ->with($expected, $storeValueMock, StoreValueInterface::class);
        }

        $this->assertEquals($expected, $this->model->resolve($storeValueMock));
    }

    /**
     * Data provider for resolve
     *
     * @return array
     */
    public function resolveDataProvider()
    {
        $storeValueMock = $this->getMockForAbstractClass(StoreValueInterface::class);
        return [
            [
                $storeValueMock,
                $storeValueMock
            ],
            [
                [
                    StoreValueInterface::STORE_ID => 1,
                    StoreValueInterface::VALUE => 'some value',
                ],
                $storeValueMock
            ]
        ];
    }
}
