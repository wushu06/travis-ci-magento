<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\RequestItemList;
use Aheadworks\Rma\Model\CustomField\Option\Action\Operation\Creditmemo as ActionCreditMemo;

/**
 * Class Creditmemo
 *
 * @package Aheadworks\Rma\Model\Request\Order
 */
class Creditmemo
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var RequestItemList
     */
    private $requestItemList;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestItemList $requestItemList
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        OrderRepositoryInterface $orderRepository,
        RequestItemList $requestItemList
    ) {
        $this->requestRepository = $requestRepository;
        $this->orderRepository = $orderRepository;
        $this->requestItemList = $requestItemList;
    }

    /**
     * Prepare items for credit memo based on RMA request
     *
     * @param int $requestId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareItems($requestId)
    {
        $request = $this->requestRepository->get($requestId);
        $order = $this->orderRepository->get($request->getOrderId());

        $requestedItems = $this->requestItemList->getForCreditMemo($request, $order, ActionCreditMemo::OPERATION);
        return $requestedItems;
    }
}
