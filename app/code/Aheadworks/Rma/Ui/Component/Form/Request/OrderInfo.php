<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Request;

use Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\OrderInfoProcessor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Field;

/**
 * Class OrderInfo
 *
 * @package Aheadworks\Rma\Ui\Component\Form\Request
 */
class OrderInfo extends Field
{
    /**
     * @var OrderInfoProcessor
     */
    private $orderInfoProcessor;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentInterface[] $components
     * @param OrderInfoProcessor $orderInfoProcessor
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderInfoProcessor $orderInfoProcessor,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderInfoProcessor = $orderInfoProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);
        $dataScope = $this->getData('config/dataScope');
        $data = $this->orderInfoProcessor->process($dataSource['data'], $dataScope);
        $dataSource['data'] = array_merge($dataSource['data'], $data);

        return $dataSource;
    }
}
