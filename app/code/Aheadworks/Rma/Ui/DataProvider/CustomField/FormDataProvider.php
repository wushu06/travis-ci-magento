<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\CustomField;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Model\ResourceModel\CustomField\CollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\CustomField\Collection;
use Aheadworks\Rma\Ui\DataProvider\StoreDataProcessor;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class FormDataProvider
 *
 * @package Aheadworks\Rma\Ui\DataProvider\CustomField
 */
class FormDataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var StoreDataProcessor
     */
    private $storeDataProcessor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param DataPersistorInterface $dataPersistor
     * @param StoreDataProcessor $storeDataProcessor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        StoreDataProcessor $storeDataProcessor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
        $this->storeDataProcessor = $storeDataProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        $dataFromForm = $this->dataPersistor->get('aw_rma_custom_field');
        $id = $this->request->getParam($this->getRequestFieldName());
        if (!empty($dataFromForm)) {
            $id = $dataFromForm['id'];
            $data = $dataFromForm;
            $this->dataPersistor->clear('aw_rma_custom_field');
        } else {
            $customFields = $this->getCollection()->addFieldToFilter('id', $id)->getItems();
            /** @var \Aheadworks\Rma\Model\CustomField $customField */
            foreach ($customFields as $customField) {
                if ($id == $customField->getId()) {
                    $data = $customField->getData();
                }
            }
        }
        $preparedData[$id] = $this->prepareData($data);

        return $preparedData;
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        $data = $this->storeDataProcessor->prepareFormData(
            $data,
            [CustomFieldInterface::FRONTEND_LABELS]
        );

        if (isset($data['id']) && !empty($data['id'])) {
            $data['disableNotEditableField'] = true;
        } else {
            $data['disableNotEditableField'] = false;
        }

        $options = [];
        if (isset($data['options']) && is_array($data['options'])) {
            $options = $data['options'];
        }

        foreach ($options as &$option) {
            $storeLabels = [];
            if (isset($option['store_labels']) && is_array($option['store_labels'])) {
                $storeLabels = $option['store_labels'];
            }

            $newStoreLabels = [];
            foreach ($storeLabels as $storeLabel) {
                $newStoreLabels[$storeLabel['store_id']] = $storeLabel;
            }
            $option['store_labels'] = $newStoreLabels;
            $option['is_new'] = false;
        }
        $data['options'] = $options;

        return $data;
    }
}
