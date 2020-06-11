<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CannedResponse;

use Aheadworks\Rma\Api\CannedResponseRepositoryInterface;
use Aheadworks\Rma\Api\Data\CannedResponseInterface;
use Aheadworks\Rma\Api\Data\CannedResponseInterfaceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Save
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\CannedResponse
 */
class Save extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::canned_responses';

    /**
     * @var CannedResponseRepositoryInterface
     */
    private $cannedResponseRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CannedResponseInterfaceFactory
     */
    private $cannedResponseFactory;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @param Context $context
     * @param CannedResponseRepositoryInterface $cannedResponseRepository
     * @param CannedResponseInterfaceFactory $cannedResponseFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     * @param PostDataProcessor $postDataProcessor
     */
    public function __construct(
        Context $context,
        CannedResponseRepositoryInterface $cannedResponseRepository,
        CannedResponseInterfaceFactory $cannedResponseFactory,
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor,
        PostDataProcessor $postDataProcessor
    ) {
        parent::__construct($context);
        $this->cannedResponseFactory = $cannedResponseFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPersistor = $dataPersistor;
        $this->cannedResponseRepository = $cannedResponseRepository;
        $this->postDataProcessor = $postDataProcessor;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $data = $this->postDataProcessor->prepareEntityData($data);
                $cannedResponse = $this->performSave($data);

                $this->dataPersistor->clear('aw_rma_canned_response');
                $this->messageManager->addSuccessMessage(__('Canned response has been successfully saved.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $cannedResponse->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the canned response.')
                );
            }
            $this->dataPersistor->set('aw_rma_canned_response', $data);
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
     * @return CannedResponseInterface
     */
    private function performSave($data)
    {
        $id = isset($data['id']) ? $data['id'] : false;
        $dataObject = $id
            ? $this->cannedResponseRepository->get($id)
            : $this->cannedResponseFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            CannedResponseInterface::class
        );

        return $this->cannedResponseRepository->save($dataObject);
    }
}
