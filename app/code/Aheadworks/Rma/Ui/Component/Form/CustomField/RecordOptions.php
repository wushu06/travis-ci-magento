<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\CustomField;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Container;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;

/**
 * Class RecordOptions
 *
 * @package Aheadworks\Rma\Ui\Component\Form\CustomField
 */
class RecordOptions extends Container
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->storeManager = $storeManager;
        $this->uiComponentFactory = $uiComponentFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $baseInputConfig = [
            'dataType' => 'text',
            'formElement' => Input::NAME,
            'validation' => []
        ];

        $stores = $this->getStores();
        foreach ($stores as $index => $store) {
            $this->createComponent(
                'store_labels_store_' . $store->getId(),
                Field::NAME,
                array_merge($baseInputConfig, $this->getInputStoreConfig($store))
            );
            $inputValueConfig = $this->getInputValueConfig($store);
            if (!isset($inputValueConfig['sortOrder'])) {
                $inputValueConfig['sortOrder'] = 80 + $index * 10;
            }
            $this->createComponent(
                'store_labels_value_' . $store->getId(),
                Field::NAME,
                array_merge($baseInputConfig, $inputValueConfig)
            );
        }
        $this->createComponent(
            'action_delete',
            ActionDelete::NAME,
            $this->getActionDeleteConfig()
        );

        parent::prepare();
    }

    /**
     * Retrieve all stores
     *
     * @return StoreInterface[]
     */
    private function getStores()
    {
        $stores = $this->storeManager->getStores(true);
        asort($stores);

        return $stores;
    }

    /**
     * Retrieve action delete config
     *
     * @return array
     */
    private function getActionDeleteConfig()
    {
        return [
            'componentType' => 'actionDelete',
            'component' => 'Aheadworks_Rma/js/ui/dynamic-rows/action-delete',
            'dataType' => 'text',
            'label' => __('Actions'),
            'template' => 'Magento_Backend/dynamic-rows/cells/action-delete',
            'imports' => [
                'visible' => '${ $.parentName }.is_new:checked'
            ],
            'additionalClasses' => [
                'control-table-options-cell' => true
            ],
            'columnsHeaderClasses' => [
                'control-table-options-th' => true
            ]
        ];
    }

    /**
     * Retrieve input store config
     *
     * @param StoreInterface $store
     * @return array
     */
    private function getInputStoreConfig($store)
    {
        return [
            'dataScope' => 'store_labels.' . $store->getId() . '.store_id',
            'additionalClasses' => [
                '_hidden' => true
            ],
            'columnsHeaderClasses' => [
                '_hidden' => true
            ]
        ];
    }

    /**
     * Retrieve input value config
     *
     * @param StoreInterface $store
     * @return array
     */
    private function getInputValueConfig($store)
    {
        $fieldValueConfig = [
            'label' => $store->getName(),
            'dataScope' => 'store_labels.' . $store->getId() . '.value'
        ];
        if ($store->getId() == Store::DEFAULT_STORE_ID) {
            $fieldValueConfig['required'] = true;
            $fieldValueConfig['sortOrder'] = 50;
            $fieldValueConfig['validation']['required-entry'] = true;
        }

        return $fieldValueConfig;
    }

    /**
     * Create component
     *
     * @param string $fieldName
     * @param string $type
     * @param array $config
     * @return $this
     */
    private function createComponent($fieldName, $type, $config)
    {
        $component = $this->uiComponentFactory->create(
            $fieldName,
            $type,
            ['context' => $this->getContext()]
        );
        $component->setData('config', $config);
        $component->prepare();
        $this->addComponent($fieldName, $component);

        return $this;
    }
}
