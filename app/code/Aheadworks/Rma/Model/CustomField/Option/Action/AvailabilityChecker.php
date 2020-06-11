<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option\Action;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Request\Resolver\Status as StatusResolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\CustomField\Option\Action\Operation\OperationInterface;

/**
 * Class ButtonProvider
 *
 * @package Aheadworks\Rma\Ui\Component\Request\Form
 */
class AvailabilityChecker
{
    /**
     * @var RequestRepositoryInterface
     */
    protected $requestRepository;

    /**
     * @var StatusResolver
     */
    protected $statusResolver;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param StatusResolver $statusResolver
     * @param \Aheadworks\Rma\Model\CustomField\Option\Action\Pool $pool
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        StatusResolver $statusResolver,
        Pool $pool
    ) {
        $this->requestRepository = $requestRepository;
        $this->statusResolver = $statusResolver;
        $this->pool = $pool;
    }

    /**
     * Check if action is available for request
     *
     * @param string $action
     * @param int $requestId
     * @param bool $causedByAdmin
     * @return bool
     * @throws \Exception
     */
    public function isAvailableAction($action, $requestId, $causedByAdmin)
    {
        if (null === $this->getRmaRequest($requestId)) {
            return false;
        }

        /** @var OperationInterface $action */
        $actionModel = $this->pool->getAction($action);
        $request = $this->getRmaRequest($requestId);

        return $actionModel->isValidForRequest($request)
            && $this->statusResolver->isAvailableActionForStatus($action, $request, $causedByAdmin);
    }

    /**
     * Retrieve RMA request
     *
     * @param int $requestId
     * @return RequestInterface|null
     */
    private function getRmaRequest($requestId)
    {
        try {
            return $this->requestRepository->get($requestId);
        } catch (NoSuchEntityException $e) {
        }

        return null;
    }
}
