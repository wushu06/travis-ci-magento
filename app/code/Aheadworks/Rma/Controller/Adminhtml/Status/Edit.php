<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Status;

use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

/**
 * Class Edit
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Status
 */
class Edit extends Action
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
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param StatusRepositoryInterface $statusRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        StatusRepositoryInterface $statusRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->statusRepository = $statusRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $statusId = (int)$this->getRequest()->getParam('id');
        if ($statusId) {
            try {
                $status = $this->statusRepository->get($statusId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('This status no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_Rma::statuses')
            ->getConfig()->getTitle()->prepend(
                $statusId ? __('Edit "%1" status', $status->getName()) : __('New status')
            );
        return $resultPage;
    }
}
