<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

/**
 * Class Edit
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class Edit extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->requestRepository = $requestRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $requestId = (int)$this->getRequest()->getParam('id');
        if ($requestId) {
            try {
                $request = $this->requestRepository->get($requestId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('This request no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_Rma::manage_rma')
            ->getConfig()->getTitle()->prepend(
                $requestId ? __('Manage Request #%1', $request->getIncrementId()) : __('New request')
            );
        return $resultPage;
    }
}
