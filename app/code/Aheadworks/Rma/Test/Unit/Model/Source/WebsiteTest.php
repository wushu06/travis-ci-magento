<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\System\Store as SystemStore;
use Aheadworks\Rma\Model\Source\Website;

/**
 * Class WebsiteTest
 * Test for \Aheadworks\Rma\Model\Source\Website
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source
 */
class WebsiteTest extends TestCase
{
    /**
     * @var Website
     */
    private $model;

    /**
     * @var SystemStore|\PHPUnit_Framework_MockObject_MockObject
     */
    private $systemStoreMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->systemStoreMock = $this->getMockBuilder(SystemStore::class)
            ->setMethods(['getWebsiteValuesForForm'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Website::class,
            [
                'systemStore' => $this->systemStoreMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $expected = [['label' => 'label', 'value' => 'value']];

        $this->systemStoreMock->expects($this->once())
            ->method('getWebsiteValuesForForm')
            ->willReturn($expected);

        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
