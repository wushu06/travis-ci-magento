<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\Config\Cms;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\Config\Cms\Block;
use Magento\Cms\Model\ResourceModel\Block\Collection as BlockCollection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;

/**
 * Class BlockTest
 * Test for \Aheadworks\Rma\Model\Source\Config\Cms\Block
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\Config\Cms
 */
class BlockTest extends TestCase
{
    /**
     * @var Block
     */
    private $model;

    /**
     * @var BlockCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $blockCollectionMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->blockCollectionMock = $this->getMockBuilder(BlockCollection::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $blockCollectionFactoryMock = $this->getMockBuilder(BlockCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $blockCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->blockCollectionMock);
        $this->model = $objectManager->getObject(
            Block::class,
            [
                'blockCollectionFactory' => $blockCollectionFactoryMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $expected = [['label' => 'label', 'value' => 'value']];

        $this->blockCollectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($expected);

        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
