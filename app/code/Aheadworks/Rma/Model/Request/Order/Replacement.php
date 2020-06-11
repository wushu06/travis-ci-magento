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
use Magento\Sales\Model\AdminOrder\Create;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\RequestItemList;
use Aheadworks\Rma\Model\CustomField\Option\Action\Operation\Replace;

/**
 * Class Replacement
 *
 * @package Aheadworks\Rma\Model\Request\Order
 */
class Replacement
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
     * @var Create
     */
    private $orderCreate;

    /**
     * @var RequestItemList
     */
    private $requestItemList;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param Create $orderCreate
     * @param RequestItemList $requestItemList
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        OrderRepositoryInterface $orderRepository,
        Create $orderCreate,
        RequestItemList $requestItemList
    ) {
        $this->requestRepository = $requestRepository;
        $this->orderRepository = $orderRepository;
        $this->orderCreate = $orderCreate;
        $this->requestItemList = $requestItemList;
    }

    /**
     * Prepare replacement order with filled up data based on RMA request
     *
     * @param int $requestId
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function prepare($requestId)
    {
        $request = $this->requestRepository->get($requestId);
        $order = $this->orderRepository->get($request->getOrderId());
        $order->setReordered(true);

        $this->orderCreate->initFromOrder($order);
        $quote = $this->orderCreate->getQuote();
        $quote->setAwRmaRequestId($requestId);
        $quote->removeAllItems();

        $requestedItems = $this->requestItemList->getForReplacement($request, $order, Replace::OPERATION);
        foreach ($requestedItems as $orderItemId => $item) {
            $this->orderCreate->addProduct(key($item), $item[key($item)]);
        }
        $this->orderCreate->saveQuote();
    }
}
