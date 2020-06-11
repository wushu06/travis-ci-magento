<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Url;

use Aheadworks\Rma\Model\Url\ParamEncryptor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class ParamEncryptorTest
 * Test for \Aheadworks\Rma\Model\Url\ParamEncryptor
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Url
 */
class ParamEncryptorTest extends TestCase
{
    /**
     * @var ParamEncryptor
     */
    private $model;

    /**
     * @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $this->encryptorMock = $this->getMockForAbstractClass(EncryptorInterface::class);
        $this->model = $objectManager->getObject(
            ParamEncryptor::class,
            [
                'encryptor' => $this->encryptorMock
            ]
        );
    }

    /**
     * Test encrypt method
     */
    public function testEncrypt()
    {
        $params = ['store_id' => 1, 'id' => '184E6D5759EA0C0A521AC'];
        $expected = 'MDoyOlhQc2c5TkhsaW9CS0xlTWpnczVwR2ptUHE0QjNMNzB5OlFqc0FEM'
            . 'lhoc2ZGOWRXZWRpaGpkY0UvUVBlUnNZekhVMlFtLzV6dHFlcUk9';
        $key = '0:2:XPsg9NHlioBKLeMjgs5pGjmPq4B3L70y:QjsAD2XhsfF9dWedihjdcE/QPeRsYzHU2Qm/5ztqeqI=';

        $this->encryptorMock->expects($this->any())
            ->method('encrypt')
            ->willReturn($key);

        $this->assertEquals($expected, $this->model->encrypt($params));
    }

    /**
     * Test decrypt method
     *
     * @param string $paramKey
     * @param mixed $expected
     * @dataProvider decryptDataProvider
     */
    public function testDecrypt($paramKey, $expected)
    {
        $key = 'MDoyOlhQc2c5TkhsaW9CS0xlTWpnczVwR2ptUHE0QjNMNzB5OlFqc0FEM'
            . 'lhoc2ZGOWRXZWRpaGpkY0UvUVBlUnNZekhVMlFtLzV6dHFlcUk9';
        $stringParams = 'store_id:1;id:184E6D5759EA0C0A521AC';

        $this->encryptorMock->expects($this->once())
            ->method('decrypt')
            ->willReturn($stringParams);

        $this->assertEquals($expected, $this->model->decrypt($paramKey, $key));
    }

    /**
     * Data provider for decrypt test
     *
     * @return array
     */
    public function decryptDataProvider()
    {
        return [['store_id', 1], ['id', '184E6D5759EA0C0A521AC'], ['hash', null]];
    }
}
