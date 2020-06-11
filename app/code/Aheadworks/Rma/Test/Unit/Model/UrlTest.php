<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model;

use Aheadworks\Rma\Model\Url;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\UrlInterface;
use Aheadworks\Rma\Model\Url\ParamEncryptor;

/**
 * Class UrlTest
 * Test for \Aheadworks\Rma\Model\Url
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 */
class UrlTest extends TestCase
{
    /**
     * @var Url
     */
    private $model;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var ParamEncryptor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encryptorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->encryptorMock = $this->getMockBuilder(ParamEncryptor::class)
            ->setMethods(['encrypt'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Url::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'encryptor' => $this->encryptorMock
            ]
        );
    }

    /**
     * Test getEncryptUrl method
     */
    public function testGetEncryptUrl()
    {
        $route = 'aw_rma/customer/printLabel';
        $params = ['store_id' => 1, 'id' => 5];
        $expected = 'http://mydomain.com/rma_220sdce/aw_rma/customer/printLabel/id/5/';
        $key = 'encryptor_key';

        $this->encryptorMock->expects($this->any())
            ->method('encrypt')
            ->willReturn($key);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with($route, ['hash' => $key])
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getEncryptUrl($route, $params));
    }
}
