<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor;

use Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\CustomerInfoProcessor;

/**
 * Class CustomerInfo
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor
 */
class CustomerInfo implements ProcessorInterface
{
    /**
     * @var CustomerInfoProcessor
     */
    private $customerInfoProcessor;

    /**
     * @param CustomerInfoProcessor $customerInfoProcessor
     */
    public function __construct(
        CustomerInfoProcessor $customerInfoProcessor
    ) {
        $this->customerInfoProcessor = $customerInfoProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($data)
    {
        $data = array_merge(
            $data,
            $this->customerInfoProcessor->process($data, 'customer_name'),
            $this->customerInfoProcessor->process($data, 'customer_email'),
            $this->customerInfoProcessor->process($data, 'customer_address')
        );

        return $data;
    }
}
