<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Customer;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Controller\CustomerAction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class View
 *
 * @package Aheadworks\Rma\Controller\Customer
 */
class View extends CustomerAction
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param CustomerSession $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        RequestRepositoryInterface $requestRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $requestRepository, $customerSession);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $requestEntity = $this->getRmaRequest();
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage
                ->getConfig()
                ->getTitle()
                ->set(__('Manage RMA Request #%1', $requestEntity->getIncrementId()));
            $this->setUrlToBackLink($resultPage);
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
