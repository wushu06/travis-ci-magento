<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Renderer;

use Aheadworks\Rma\Model\Renderer\CmsBlock;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Filter\Template;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Class CmsBlockTest
 * Test for \Aheadworks\Rma\Model\Renderer\CmsBlock
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Renderer
 */
class CmsBlockTest extends TestCase
{
    /**
     * @var CmsBlock
     */
    private $model;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var BlockRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cmsBlockRepositoryMock;

    /**
     * @var FilterProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cmsFilterProviderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->cmsBlockRepositoryMock = $this->getMockForAbstractClass(BlockRepositoryInterface::class);
        $this->cmsFilterProviderMock = $this->getMockBuilder(FilterProvider::class)
            ->setMethods(['getBlockFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            CmsBlock::class,
            [
                'storeManager' => $this->storeManagerMock,
                'cmsBlockRepository' => $this->cmsBlockRepositoryMock,
                'cmsFilterProvider' => $this->cmsFilterProviderMock
            ]
        );
    }

    /**
     * Test render method
     *
     * @param int $storeId
     * @dataProvider renderDataProvider
     */
    public function testRender($storeId)
    {
        $bockId = 1;
        $blockIsActive = true;
        $content = 'block content';
        
        if (empty($storeId)) {
            $expectedStoreId = 1;
            $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->willReturn($storeMock);
            $storeMock->expects($this->once())
                ->method('getId')
                ->willReturn($expectedStoreId);
        } else {
            $expectedStoreId = $storeId;
        }

        $blockMock = $this->getMockForAbstractClass(BlockInterface::class);
        $this->cmsBlockRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($bockId)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())
            ->method('isActive')
            ->willReturn($blockIsActive);

        $blockMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $filterTemplateMock = $this->getMockBuilder(Template::class)
            ->setMethods(['setStoreId', 'filter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cmsFilterProviderMock->expects($this->once())
            ->method('getBlockFilter')
            ->willReturn($filterTemplateMock);

        $filterTemplateMock->expects($this->once())
            ->method('setStoreId')
            ->with($expectedStoreId)
            ->willReturnSelf();
        $filterTemplateMock->expects($this->once())
            ->method('filter')
            ->with($content)
            ->willReturn($content);

        $this->assertEquals($content, $this->model->render($bockId, $storeId));
    }

    /**
     * Test render method
     */
    public function testRenderBlockNotActive()
    {
        $bockId = 1;
        $content = '';
        $blockIsActive = false;
        $storeId = 1;

        $blockMock = $this->getMockForAbstractClass(BlockInterface::class);
        $this->cmsBlockRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($bockId)
            ->willReturn($blockMock);
        $blockMock->expects($this->once())
            ->method('isActive')
            ->willReturn($blockIsActive);

        $this->assertEquals($content, $this->model->render($bockId, $storeId));
    }

    /**
     * Data provider for render test
     *
     * @return array
     */
    public function renderDataProvider()
    {
        return [[1], [null]];
    }
}
