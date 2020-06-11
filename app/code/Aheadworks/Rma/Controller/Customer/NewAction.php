<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class NewAction
 *
 * @package Aheadworks\Rma\Controller\Customer
 */
class NewAction extends Action
{
    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->customerSession = $customerSession;
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
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        if ($this->getRequest()->getParam('order_id')) {
            return $resultForward->forward('createRequestStep');
        }
        return $resultForward->forward('selectOrderStep');
    }
}
