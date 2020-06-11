<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request;

use Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\Request\Collection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class FormDataProvider
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request
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
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param DataPersistorInterface $dataPersistor
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
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        $dataFromForm = $this->dataPersistor->get('aw_rma_request');
        $id = $this->request->getParam($this->getRequestFieldName());
        if (!empty($dataFromForm)) {
            $id = $dataFromForm['id'];
            $data = $this->prepareDataAfterError($dataFromForm);
            $this->dataPersistor->clear('aw_rma_request');
        } else {
            $requests = $this->getCollection()->addFieldToFilter('id', $id)->getItems();
            /** @var \Aheadworks\Rma\Model\Request $request */
            foreach ($requests as $request) {
                if ($id == $request->getId()) {
                    $data = $request->getData();
                }
            }
        }
        $preparedData[$id] = $this->prepareData($data);

        return $preparedData;
    }

    /**
     * Prepare data after error
     *
     * @param array $data
     * @return array
     */
    private function prepareDataAfterError($data)
    {
        $data['thread_message'] = isset($data['thread_message']) && !empty($data['thread_message'])
            ? $data['thread_message']
            : '';
        $data['error_after_save'] = true;

        return $data;
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        if (isset($data['id']) && !empty($data['id'])) {
            $data['newRequest'] = false;
            $data['editRequest'] = true;
        } else {
            if ($orderId = $this->request->getParam('order_id')) {
                $data['sales_order_selected'] = [
                    0 => ['entity_id' => $orderId]
                ];
            }
            $data['newRequest'] = true;
            $data['editRequest'] = false;
        }
        $data = $this->prepareCustomFields($data);

        if (isset($data['order_items'])) {
            foreach ($data['order_items'] as &$orderItem) {
                $orderItem = $this->prepareCustomFields($orderItem);
            }
        }

        return $data;
    }

    /**
     * Prepare custom fields for item
     *
     * @param array $data
     * @return array
     */
    private function prepareCustomFields($data)
    {
        if (!isset($data['custom_fields'])) {
            return $data;
        }

        $preparedCustomFields = [];
        foreach ($data['custom_fields'] as $customField) {
            $preparedCustomFields[$customField['field_id']] = $customField['value'];
        }
        $data['custom_fields'] = $preparedCustomFields;

        return $data;
    }
}
