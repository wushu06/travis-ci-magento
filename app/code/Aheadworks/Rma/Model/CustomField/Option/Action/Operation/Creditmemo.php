<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option\Action\Operation;

use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Creditmemo
 *
 * @package Aheadworks\Rma\Model\CustomField\Option\Action\Operation
 */
class Creditmemo implements OperationInterface
{
    /**
     * Action operation
     */
    const OPERATION = 'creditmemo';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function isValidForRequest($request)
    {
        $result = false;
        $orderId = $request->getOrderId();
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $result = $order->canCreditmemo();
        }
        return $result;
    }
}
