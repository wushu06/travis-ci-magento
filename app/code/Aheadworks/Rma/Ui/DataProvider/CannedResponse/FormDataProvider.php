<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\CannedResponse;

use Aheadworks\Rma\Model\ResourceModel\CannedResponse\CollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\CannedResponse\Collection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class FormDataProvider
 *
 * @package Aheadworks\Rma\Ui\DataProvider\CannedResponse
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
        $dataFromForm = $this->dataPersistor->get('aw_rma_canned_response');
        $id = $this->request->getParam($this->getRequestFieldName());
        if (!empty($dataFromForm) && isset($dataFromForm['id'])) {
            $id = $dataFromForm['id'];
            $data = $dataFromForm;
            $this->dataPersistor->clear('aw_rma_canned_response');
        } else {
            $cannedResponseCollection = $this->getCollection()->addFieldToFilter('id', $id)->getItems();
            /** @var \Aheadworks\Rma\Model\CannedResponse $cannedResponse */
            foreach ($cannedResponseCollection as $cannedResponse) {
                if ($id == $cannedResponse->getId()) {
                    $data = $cannedResponse->getData();
                }
            }
        }
        $preparedData[$id] = $this->prepareCannedResponseData($data);

        return $preparedData;
    }

    /**
     * Retrieve array with prepared canned response data
     *
     * @param array $cannedResponseData
     * @return array
     */
    private function prepareCannedResponseData($cannedResponseData)
    {
        if (isset($data['id']) && !empty($data['id'])) {
            $cannedResponseData['newResponse'] = false;
            $cannedResponseData['editResponse'] = true;
        } else {
            $cannedResponseData['newResponse'] = true;
            $cannedResponseData['editResponse'] = false;
        }
        return $cannedResponseData;
    }
}
