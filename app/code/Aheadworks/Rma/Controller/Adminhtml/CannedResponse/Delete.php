<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CannedResponse;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Aheadworks\Rma\Api\CannedResponseRepositoryInterface;

/**
 * Class Delete
 * @package Aheadworks\Rma\Controller\Adminhtml\CannedResponse
 */
class Delete extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::canned_responses';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var CannedResponseRepositoryInterface
     */
    protected $cannedResponseRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CannedResponseRepositoryInterface $cannedResponseRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CannedResponseRepositoryInterface $cannedResponseRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->cannedResponseRepository = $cannedResponseRepository;
    }

    /**
     * Delete canned response action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $cannedResponseId = (int) $this->getRequest()->getParam('id');
        if ($cannedResponseId) {
            try {
                $this->cannedResponseRepository->deleteById($cannedResponseId);
                $this->messageManager->addSuccessMessage(__('Canned response has been successfully deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('Something went wrong while deleting canned response'));
        return $resultRedirect->setPath('*/*/');
    }
}
