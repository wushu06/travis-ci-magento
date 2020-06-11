<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class GuestAction
 *
 * @package Aheadworks\Rma\Controller\Customer
 */
abstract class GuestAction extends AbstractAction
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param Config $config
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        Config $config
    ) {
        parent::__construct($context, $requestRepository);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->config->isAllowGuestsCreateRequest()) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRefererUrl()
    {
        return $this->_url->getUrl();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRmaRequest()
    {
        try {
            $requestEntity = $this->requestRepository->getByExternalLink($this->getRequest()->getParam('id'));
        } catch (NoSuchEntityException $e) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $requestEntity;
    }
}
