<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Request;

use Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\CustomerInfoProcessor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;

/**
 * Class CustomerInfo
 *
 * @package Aheadworks\Rma\Ui\Component\Form\Request
 */
class CustomerInfo extends Field
{
    /**
     * @var CustomerInfoProcessor
     */
    private $customerInfoProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerInfoProcessor $customerInfoProcessor
     * @param StoreManagerInterface $storeManager
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerInfoProcessor $customerInfoProcessor,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->customerInfoProcessor = $customerInfoProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if ((!$this->storeManager->isSingleStoreMode() || count($this->storeManager->getWebsites()) > 1)
            && $config['dataScope'] == 'customer_previous_orders'
        ) {
            $config['label'] = __('Previous Orders (%1)', $this->storeManager->getWebsite()->getName());
        }
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);
        $dataScope = $this->getData('config/dataScope');
        $data = $this->customerInfoProcessor->process($dataSource['data'], $dataScope);
        $dataSource['data'] = array_merge($dataSource['data'], $data);

        return $dataSource;
    }
}
