<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CustomField;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterfaceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Save
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\CustomField
 */
class Save extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::custom_fields';

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var CustomFieldInterfaceFactory
     */
    private $customFieldFactory;

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @param Context $context
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param CustomFieldInterfaceFactory $customFieldFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     * @param PostDataProcessor $postDataProcessor
     */
    public function __construct(
        Context $context,
        CustomFieldRepositoryInterface $customFieldRepository,
        CustomFieldInterfaceFactory $customFieldFactory,
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor,
        PostDataProcessor $postDataProcessor
    ) {
        parent::__construct($context);
        $this->customFieldFactory = $customFieldFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPersistor = $dataPersistor;
        $this->customFieldRepository = $customFieldRepository;
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
                $customField = $this->performSave($data);

                $this->dataPersistor->clear('aw_rma_custom_field');
                $this->messageManager->addSuccessMessage(__('Custom field was successfully saved.'));

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $customField->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the custom field.')
                );
            }
            $this->dataPersistor->set('aw_rma_custom_field', $data);
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
     * @return CustomFieldInterface
     */
    private function performSave($data)
    {
        $id = isset($data['id']) ? $data['id'] : false;
        $dataObject = $id
            ? $this->customFieldRepository->get($id)
            : $this->customFieldFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $dataObject,
            $data,
            CustomFieldInterface::class
        );

        return $this->customFieldRepository->save($dataObject);
    }
}
