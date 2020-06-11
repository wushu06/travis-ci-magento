<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Aheadworks\Rma\Api\RequestManagementInterface;
use Aheadworks\Rma\Model\Request\PostDataProcessor\Composite as RequestPostDataProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Action;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory as RmaRequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestInterface as RmaRequestInterface;

/**
 * Class Save
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class Save extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RequestManagementInterface
     */
    private $requestManagement;

    /**
     * @var RequestPostDataProcessor
     */
    private $requestPostDataProcessor;

    /**
     * @var RmaRequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param RequestManagementInterface $requestManagement
     * @param RequestPostDataProcessor $requestPostDataProcessor
     * @param RmaRequestInterfaceFactory $requestFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataObjectHelper $dataObjectHelper,
        RequestManagementInterface $requestManagement,
        RequestPostDataProcessor $requestPostDataProcessor,
        RmaRequestInterfaceFactory $requestFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->requestManagement = $requestManagement;
        $this->requestPostDataProcessor = $requestPostDataProcessor;
        $this->requestFactory = $requestFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $data = $this->requestPostDataProcessor->prepareEntityData($data);
                $requestEntity = $this->performSave($data);
                $this->dataPersistor->clear('aw_rma_request');
                $this->messageManager->addSuccessMessage(__('Return has been successfully saved.'));
                $redirectPath = isset($data['redirect_path']) ? $data['redirect_path'] : false;
                if ($redirectPath) {
                    return $resultRedirect->setPath(
                        '*/*/action_' . $redirectPath,
                        ['request_id' => $requestEntity->getId()]
                    );
                }
                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $requestEntity->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the return.'));
            }
            $this->dataPersistor->set('aw_rma_request', $data);
            $id = isset($data['id']) ? $data['id'] : false;
            if ($id) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $id, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return RmaRequestInterface
     * @throws LocalizedException
     */
    private function performSave($data)
    {
        $requestObject = $this->requestFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $requestObject,
            $data,
            RmaRequestInterface::class
        );

        if ($this->isUpdateRequest($data)) {
            $request = $this->requestManagement->updateRequest($requestObject, true);
        } else {
            $request = $this->requestManagement->createRequest($requestObject, true);
        }

        return $request;
    }

    /**
     * Check if update request
     *
     * @param array $data
     * @return bool
     */
    private function isUpdateRequest($data)
    {
        return isset($data['id']) && !empty($data['id']);
    }
}
