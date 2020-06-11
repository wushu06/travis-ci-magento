<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Request;

use Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\OrderItemInfoProcessor;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Container;

/**
 * Class OrderItemInfo
 *
 * @package Aheadworks\Rma\Ui\Component\Form\Request
 */
class OrderItemInfo extends Container
{
    /**
     * @var OrderItemInfoProcessor
     */
    private $orderItemInfoProcessor;

    /**
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param OrderItemInfoProcessor $orderItemInfoProcessor
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        OrderItemInfoProcessor $orderItemInfoProcessor,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->orderItemInfoProcessor = $orderItemInfoProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);
        if (isset($dataSource['data']['order_items']) && $dataSource['data']['order_items']) {
            $dataSource['data']['order_items'] = $this->orderItemInfoProcessor->process(
                $dataSource['data']['order_items'],
                $dataSource['data']['store_id']
            );
        }
        return $dataSource;
    }
}
