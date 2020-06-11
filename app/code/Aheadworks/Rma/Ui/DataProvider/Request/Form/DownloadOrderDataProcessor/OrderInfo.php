<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor;

use Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\OrderInfoProcessor;

/**
 * Class OrderInfo
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor
 */
class OrderInfo implements ProcessorInterface
{
    /**
     * @var OrderInfoProcessor
     */
    private $orderInfoProcessor;

    /**
     * @param OrderInfoProcessor $orderInfoProcessor
     */
    public function __construct(
        OrderInfoProcessor $orderInfoProcessor
    ) {
        $this->orderInfoProcessor = $orderInfoProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($data)
    {
        $preparedData = $this->orderInfoProcessor->process($data, 'order_increment_id');
        $data = array_merge($data, $preparedData);

        return $data;
    }
}
