<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Status;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Model\ResourceModel\Status\CollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\Status\Collection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Aheadworks\Rma\Ui\DataProvider\StoreDataProcessor;

/**
 * Class FormDataProvider
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Status
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
        $dataFromForm = $this->dataPersistor->get('aw_rma_status');
        $id = $this->request->getParam($this->getRequestFieldName());
        if (!empty($dataFromForm)) {
            $id = $dataFromForm['id'];
            $data = $dataFromForm;
            $this->dataPersistor->clear('aw_rma_status');
        } else {
            $statuses = $this->getCollection()->addFieldToFilter('id', $id)->getItems();
            /** @var \Aheadworks\Rma\Model\Status $status */
            foreach ($statuses as $status) {
                if ($id == $status->getId()) {
                    $data = $status->getData();
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
            [
                StatusInterface::FRONTEND_LABELS,
                StatusInterface::CUSTOMER_TEMPLATES,
                StatusInterface::ADMIN_TEMPLATES,
                StatusInterface::THREAD_TEMPLATES
            ]
        );

        if (isset($data['id']) && !empty($data['id'])) {
            $data['visibleStateIsSetId'] = true;
            $data['visibleStateIsNotSetId'] = false;
        } else {
            $data['visibleStateIsSetId'] = false;
            $data['visibleStateIsNotSetId'] = true;
        }

        return $data;
    }
}
