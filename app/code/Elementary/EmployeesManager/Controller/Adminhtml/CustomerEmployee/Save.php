<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Controller\Adminhtml\CustomerEmployee;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Elementary\EmployeesManager\Model\CustomerEmployeeFactory;

class Save extends Action
{
    /**
     * @var CustomerEmployeeFactory
     */
    protected $customeremployeeFactory;

    /**
     * [__construct description]
     * @param  Context           $context           [description]
     * @param  CustomerEmployeeFactory $customeremployeeFactory [description]
     */
    public function __construct(
        Context $context,
        CustomerEmployeeFactory $customeremployeeFactory
    ) {
        $this->customeremployeeFactory = $customeremployeeFactory;
        parent::__construct($context);
    }

    /**
     * For allow to access or not
     *
     * return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Elementary_EmployeesManager::customeremployee');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $data = $this->getRequest()->getParams();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $params = [];
            $customeremployeeData = $this->customeremployeeFactory->create();
            $customeremployeeData->setStoreId($storeId);
            $params['store'] = $storeId;
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            } else {
                $customeremployeeData->load($data['entity_id']);
                $params['entity_id'] = $data['entity_id'];
            }
            $customeremployeeData->addData($data);

            $this->_eventManager->dispatch(
                'elementary_employeesmanager_customeremployee_prepare_save',
                ['object' => $this->customeremployeeFactory, 'request' => $this->getRequest()]
            );

            try {
                $customeremployeeData->save();
                $this->messageManager->addSuccessMessage(__('You saved this record.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $params['entity_id'] = $customeremployeeData->getId();
                    $params['_current'] = true;
                    return $resultRedirect->setPath('*/*/edit', $params);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the record.'));
            }

            $this->_getSession()->setFormData($this->getRequest()->getPostValue());
            return $resultRedirect->setPath('*/*/edit', $params);
        }
        return $resultRedirect->setPath('*/*/');
    }

}