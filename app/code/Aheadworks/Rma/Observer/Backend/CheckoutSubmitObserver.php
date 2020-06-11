<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Aheadworks\Rma\Api\Data\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Model\Request\Order\Storage\CurrentOrder;
use Aheadworks\Rma\Model\ThreadMessage\Action\MessageService;

/**
 * Class CheckoutSubmitObserver
 *
 * @package Aheadworks\Rma\Observer\Backend
 */
class CheckoutSubmitObserver implements ObserverInterface
{
    /**
     * @var MessageService
     */
    private $messageService;

    /**
     * @var CurrentOrder
     */
    private $currentOrder;

    /**
     * @param MessageService $messageService
     * @param CurrentOrder $currentOrder
     */
    public function __construct(
        MessageService $messageService,
        CurrentOrder $currentOrder
    ) {
        $this->messageService = $messageService;
        $this->currentOrder = $currentOrder;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getAwRmaRequestId()) {
            $this->messageService->addNewReplacementOrderMessage($order->getAwRmaRequestId(), $order);
            $this->currentOrder->setOrder($order);
        }
    }
}
