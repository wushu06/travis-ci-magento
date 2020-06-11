<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class CreateRequestStep
 *
 * @package Aheadworks\Rma\Controller\Customer
 */
class CreateRequestStep extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerSession $customerSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            $this->customerSession->setAwRmaRequestOrderId($orderId);

            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage
                ->getConfig()
                ->getTitle()
                ->set(__('New Return for Order #%1', $order->getIncrementId()));
            $this->setUrlToBackLink($resultPage);
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while loading the page.'));
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Set url to back link
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return $this
     */
    private function setUrlToBackLink($resultPage)
    {
        /** @var \Aheadworks\Rma\Block\BackLink $linkBack */
        $linkBack = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($linkBack) {
            $linkBack->setRefererUrl($this->_url->getUrl('*/*/'));
        }
        return $this;
    }
}
