<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CannedResponse;

use Aheadworks\Rma\Api\CannedResponseRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

/**
 * Class Edit
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\CannedResponse
 */
class Edit extends Action
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
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param CannedResponseRepositoryInterface $cannedResponseRepository;
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        CannedResponseRepositoryInterface $cannedResponseRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->cannedResponseRepository = $cannedResponseRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $cannedResponseId = (int) $this->getRequest()->getParam('id');
        if ($cannedResponseId) {
            try {
                $cannedResponse = $this->cannedResponseRepository->get($cannedResponseId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('This canned response no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_Rma::canned_responses')
            ->getConfig()->getTitle()->prepend(
                $cannedResponseId
                    ? __('Edit "%1" canned response', $cannedResponse->getTitle())
                    : __('New canned response')
            );
        return $resultPage;
    }
}
