<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Block\BackLink;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\UrlInterface;

/**
 * Class BackLinkTest
 * Test for \Aheadworks\Rma\Block\BackLink
 *
 * @package Aheadworks\Rma\Test\Unit\Block
 */
class BackLinkTest extends TestCase
{
    /**
     * @var BackLink
     */
    private $block;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $contextMock = $objectManager->getObject(
            Context::class,
            ['urlBuilder' => $this->urlBuilderMock]
        );
        $this->block = $objectManager->getObject(
            BackLink::class,
            ['context' => $contextMock]
        );
    }

    /**
     * Test getBackUrl method
     */
    public function testGetBackUrl()
    {
        $expected = 'url';

        $this->block->setRefererUrl($expected);

        $this->assertEquals($expected, $this->block->getBackUrl());
    }

    /**
     * Test getBackUrl method, referer url is not set
     */
    public function testGetBackUrlNotSetUrl()
    {
        $expected = 'url';

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('', [])
            ->willReturn($expected);

        $this->assertEquals($expected, $this->block->getBackUrl());
    }
}
