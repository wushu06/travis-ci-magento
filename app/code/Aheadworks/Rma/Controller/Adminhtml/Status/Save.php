<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Status;

use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\StatusInterfaceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Aheadworks\Rma\Model\Status\PostDataProcessor\Status as PostDataProcessor;

/**
 * Class Save
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Status
 */
class Save extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::statuses';

    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var StatusInterfaceFactory
     */
    private $statusFactory;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @param Context $context
     * @param StatusRepositoryInterface $statusRepository
     * @param StatusInterfaceFactory $statusFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     * @param PostDataProcessor $postDataProcessor
     */
    public function __construct(
        Context $context,
        StatusRepositoryInterface $statusRepository,
        StatusInterfaceFactory $statusFactory,
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor,
        PostDataProcessor $postDataProcessor
    ) {
        parent::__construct($context);
        $this->statusFactory = $statusFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPersistor = $dataPersistor;
        $this->statusRepository = $statusRepository;
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
                $status = $this->performSave($data);

                $this->dataPersistor->clear('aw_rma_status');
                $this->messageManager->addSuccessMessage(__('Status was successfully saved.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $status->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the status.')
                );
            }
            $this->dataPersistor->set('aw_rma_status', $data);
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
     * @return StatusInterface
     */
    private function performSave($data)
    {
        $id = isset($data['id']) ? $data['id'] : false;
        $dataObject = $id
            ? $this->statusRepository->get($id)
            : $this->statusFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            StatusInterface::class
        );

        return $this->statusRepository->save($dataObject);
    }
}
