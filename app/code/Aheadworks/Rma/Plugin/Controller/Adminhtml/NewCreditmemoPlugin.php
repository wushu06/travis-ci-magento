<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Plugin\Controller\Adminhtml;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\NewAction;
use Magento\Framework\View\Result\Page;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class NewCreditmemoPlugin
 *
 * @package Aheadworks\Rma\Plugin\Controller\Adminhtml
 */
class NewCreditmemoPlugin
{
    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Catch an error in case credit memo is not allowed
     *
     * @param NewAction $subject
     * @param callable $proceed
     * @return Page|Forward|Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute($subject, callable $proceed)
    {
        $requestId = $subject->getRequest()->getParam('request_id', false);
        if ($requestId) {
            try {
                $result = $proceed();
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage(__($exception->getMessage()));
                /** @var Redirect $resultRedirect */
                $redirect = $this->resultRedirectFactory->create();
                $redirect->setPath('aw_rma_admin/rma/edit', ['id' => $requestId]);
                $result = $redirect;
            }
        } else {
            $result = $proceed();
        }

        return $result;
    }
}
