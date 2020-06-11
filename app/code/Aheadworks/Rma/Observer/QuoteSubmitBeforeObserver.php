<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Aheadworks\Rma\Api\Data\CartInterface;
use Aheadworks\Rma\Api\Data\OrderInterface;

/**
 * Class QuoteSubmitBeforeObserver
 *
 * @package Aheadworks\Rma\Observer
 */
class QuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     *  {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var $order OrderInterface **/
        $order = $event->getOrder();
        /** @var $quote CartInterface */
        $quote = $event->getQuote();

        if ($quote->getAwRmaRequestId()) {
            $order->setAwRmaRequestId($quote->getAwRmaRequestId());
        }
    }
}
