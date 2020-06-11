<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Service;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\RequestManagementInterface;
use Aheadworks\Rma\Api\CustomerRequestManagementInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;

/**
 * Class RequestService
 *
 * @package Aheadworks\Rma\Model\Service
 */
class CustomerRequestService implements CustomerRequestManagementInterface
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var RequestManagementInterface
     */
    private $requestService;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param RequestManagementInterface $requestService
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        RequestManagementInterface $requestService
    ) {
        $this->requestRepository = $requestRepository;
        $this->requestService = $requestService;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(RequestInterface $request, $storeId = null)
    {
        return $this->requestService->createRequest($request, false, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRequest(RequestInterface $request, $storeId = null)
    {
        return $this->requestService->updateRequest($request, false, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestPrintLabelUrl($externalLink, $storeId = null)
    {
        $request = $this->getRequest($externalLink);
        return $this->requestService->getPrintLabelUrl($request->getId(), $storeId);
    }
}
