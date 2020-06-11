<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit\Button;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Rma\Model\Request\Resolver\Status as StatusResolver;
use Magento\Backend\Block\Widget\Context;
use Aheadworks\Rma\Model\Status\Request\StatusList;

/**
 * Class ButtonAbstract
 *
 * @package Aheadworks\Rma\Block\Adminhtml\Request\Edit\Button
 */
abstract class ButtonAbstract
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var RequestRepositoryInterface
     */
    protected $requestRepository;

    /**
     * @var StatusResolver
     */
    protected $statusResolver;

    /**
     * @var StatusList
     */
    protected $statusList;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param StatusResolver $statusResolver
     * @param StatusList $statusList
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        StatusResolver $statusResolver,
        StatusList $statusList
    ) {
        $this->context = $context;
        $this->requestRepository = $requestRepository;
        $this->statusResolver = $statusResolver;
        $this->statusList = $statusList;
    }

    /**
     * Check is available action
     *
     * @param string $action
     * @return bool
     */
    protected function isAvailableAction($action)
    {
        if (null === $this->getRmaRequest()) {
            return false;
        }

        return $this->statusResolver->isAvailableActionForStatus($action, $this->getRmaRequest(), true);
    }

    /**
     * Retrieve RMA request
     *
     * @return \Aheadworks\Rma\Api\Data\RequestInterface|null
     */
    protected function getRmaRequest()
    {
        try {
            return $this->requestRepository->get($this->context->getRequest()->getParam('id'));
        } catch (NoSuchEntityException $e) {
        }

        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
