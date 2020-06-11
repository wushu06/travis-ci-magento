<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Aheadworks\Rma\Ui\DataProvider\Request\Form\DownloadOrderDataProcessor\Composite as DownloadOrderDataProcessor;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class DownloadOrderData
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class DownloadOrderData extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var DownloadOrderDataProcessor
     */
    private $downloadOrderDataProcessor;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param FormKey $formKey
     * @param DownloadOrderDataProcessor $downloadOrderDataProcessor
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        FormKey $formKey,
        DownloadOrderDataProcessor $downloadOrderDataProcessor,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKey = $formKey;
        $this->downloadOrderDataProcessor = $downloadOrderDataProcessor;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Preview action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $result = ['error' => true, 'message' => __('Invalid response data.')];

        $orderId = $this->getRequest()->getParam('entity_id');
        if ($this->isValidRequestData() && $orderId) {
            try {
                $orderData = $this->getInitialOrderData($orderId);
                $orderData = $this->downloadOrderDataProcessor->prepare($orderData);
                $result = ['error' => false, 'data' => $orderData];
            } catch (\Exception $e) {
                $result = ['error' => true, 'message' => __($e->getMessage())];
            }
        }

        return $resultJson->setData($result);
    }

    /**
     * Is valid request data
     *
     * @return bool
     */
    private function isValidRequestData()
    {
        return $this->getRequest()->isAjax()
            && $this->getRequest()->getParam('form_key') == $this->formKey->getFormKey();
    }

    /**
     * Retrieve initial order data
     *
     * @param int $orderId
     * @return array
     */
    private function getInitialOrderData($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        return [
            'order_id' => $order->getEntityId(),
            'store_id' => $order->getStoreId()
        ];
    }
}
