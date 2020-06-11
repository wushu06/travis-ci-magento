<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\Request;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class PaymentMethod
 *
 * @package Aheadworks\Rma\Model\Source\Request
 */
class PaymentMethod implements ArrayInterface
{
    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->paymentHelper->getPaymentMethodList() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }
}
