<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class CustomerAction
 *
 * @package Aheadworks\Rma\Controller\Customer
 */
abstract class CustomerAction extends AbstractAction
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        CustomerSession $customerSession
    ) {
        parent::__construct($context, $requestRepository);
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
            return parent::dispatch($request);
        }

        if (!$this->isRequestBelongsToCustomer()) {
            throw new NotFoundException(__('Page not found.'));
        }

        return parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRefererUrl()
    {
        return $this->_url->getUrl('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRmaRequest()
    {
        try {
             $requestEntity = $this->requestRepository->get($this->getRequest()->getParam('id'));
        } catch (NoSuchEntityException $e) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $requestEntity;
    }

    /**
     * Check if RMA request belongs to current customer
     *
     * @return bool
     * @throws NotFoundException
     */
    private function isRequestBelongsToCustomer()
    {
        $request = $this->getRmaRequest();
        if ($request->getId() && $request->getCustomerId() == $this->customerSession->getCustomerId()) {
            return true;
        }

        return false;
    }
}
