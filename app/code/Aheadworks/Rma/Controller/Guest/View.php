<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Guest;

use Aheadworks\Rma\Controller\GuestAction;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class View
 *
 * @package Aheadworks\Rma\Controller\Guest
 */
class View extends GuestAction
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param Config $config
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        Config $config,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $requestRepository, $config);
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
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
