<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\Request\PaymentMethod;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class PaymentMethodTest
 * Test for \Aheadworks\Rma\Model\Source\Request\PaymentMethod
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\Request
 */
class PaymentMethodTest extends TestCase
{
    /**
     * @var PaymentMethod
     */
    private $model;

    /**
     * @var PaymentHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentHelperMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->paymentHelperMock = $this->getMockBuilder(PaymentHelper::class)
            ->setMethods(['getPaymentMethodList'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            PaymentMethod::class,
            [
                'paymentHelper' => $this->paymentHelperMock
            ]
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $expected = [['label' => 'label', 'value' => 'value']];

        $this->paymentHelperMock->expects($this->once())
            ->method('getPaymentMethodList')
            ->willReturn($expected);

        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
