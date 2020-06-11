<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Controller\Adminhtml\CustomerEmployee;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Elementary\EmployeesManager\Model\CustomerEmployeeFactory;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;


    /**
     * @var CustomerEmployeeFactory
     */
    protected $customeremployeeFactory;

    /**
     * [__construct description]
     * @param  Context           $context           [description]
     * @param  PageFactory       $resultPageFactory [description]
     * @param  Registry          $registry          [description]
     * @param  CustomerEmployeeFactory $customeremployeeFactory [description]
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        CustomerEmployeeFactory $customeremployeeFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
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
     * Edit
     *
     * @return \Magento\Backend\Model\View\Result\Page | \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $customeremployeeData = $this->customeremployeeFactory->create();

        if ($id) {
            $customeremployeeData->load($id);
            if (!$customeremployeeData->getId()) {
                $this->messageManager->addErrorMessage(__('This record no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $customeremployeeData->addData($data);
        }

       // $this->_coreRegistry->register('entity_id', $id);
        $this->_coreRegistry->register('customeremployee', $customeremployeeData);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Elementary_EmployeesManager::customeremployee');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Record'));

        return $resultPage;
    }
}
