<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class AbstractAction
 *
 * @package Aheadworks\Rma\Controller\Customer
 */
abstract class AbstractAction extends Action
{
    /**
     * @var RequestRepositoryInterface
     */
    protected $requestRepository;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository
    ) {
        parent::__construct($context);
        $this->requestRepository = $requestRepository;
    }

    /**
     * Retrieve RMA request
     *
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     * @throws NotFoundException
     */
    abstract protected function getRmaRequest();

    /**
     * Retrieve referer url
     *
     * @return string
     */
    abstract protected function getRefererUrl();

    /**
     * Set url to back link
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return $this
     */
    protected function setUrlToBackLink($resultPage)
    {
        /** @var \Aheadworks\Rma\Block\BackLink $linkBack */
        $linkBack = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($linkBack) {
            $linkBack->setRefererUrl($this->getRefererUrl());
        }
        return $this;
    }
}
