<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Block\FooterLink;
use Magento\Customer\Model\Session as CustomerSession;
use Aheadworks\Rma\Model\Config;

/**
 * Class FooterLinkTest
 * Test for \Aheadworks\Rma\Block\FooterLink
 *
 * @package Aheadworks\Rma\Test\Unit\Block
 */
class FooterLinkTest extends TestCase
{
    /**
     * @var FooterLink
     */
    private $block;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isAllowGuestsCreateRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(CustomerSession::class)
            ->setMethods(['isLoggedIn'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test addLink method, customer logged in
     */
    public function testAddLinkCustomerLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(
            FooterLink::class,
            [
                'config' => $this->configMock,
                'customerSession' => $this->customerSessionMock
            ]
        );
    }

    /**
     * Test addLink method, customer not logged in
     *
     * @param bool $isAllowGuestsCreateRequest
     * @dataProvider boolDataProvider
     */
    public function testAddLinkCustomerNotLoggedIn($isAllowGuestsCreateRequest)
    {
        $this->configMock->expects($this->once())
            ->method('isAllowGuestsCreateRequest')
            ->willReturn($isAllowGuestsCreateRequest);

        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(
            FooterLink::class,
            [
                'config' => $this->configMock,
                'customerSession' => $this->customerSessionMock
            ]
        );
    }

    /**
     * Bool data provider
     *
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
