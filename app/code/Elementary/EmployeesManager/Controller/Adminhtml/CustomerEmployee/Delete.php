<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Controller\Adminhtml\CustomerEmployee;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Elementary\EmployeesManager\Model\CustomerEmployeeFactory;

class Delete extends Action
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
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Elementary_EmployeesManager::customeremployee');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id', null);

        try {
            $helloWorldData = $this->customeremployeeFactory->create()->load($id);
            if ($helloWorldData->getId()) {
                $helloWorldData->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the record.'));
            } else {
                $this->messageManager->addErrorMessage(__('Record does not exist.'));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect->setPath('*/*');
    }
}