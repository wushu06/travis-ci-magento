<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Plugin\Controller\Adminhtml;

use Magento\Sales\Controller\Adminhtml\Order\Create\Save;
use Aheadworks\Rma\Model\Request\Order\Storage\CurrentOrder;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class OrderCreateSavePlugin
 *
 * @package Aheadworks\Rma\Plugin\Controller\Adminhtml
 */
class OrderCreateSavePlugin
{
    /**
     * @var CurrentOrder
     */
    private $currentOrder;

    /**
     * @param CurrentOrder $currentOrder
     */
    public function __construct(
        CurrentOrder $currentOrder
    ) {
        $this->currentOrder = $currentOrder;
    }

    /**
     * Make redirect back to rma request
     *
     * @param Save $subject
     * @param Redirect $result
     * @return Redirect $result
     */
    public function afterExecute($subject, $result)
    {
        $order = $this->currentOrder->getOrder();
        if ($order && $order->getAwRmaRequestId()) {
            $result->setPath('aw_rma_admin/rma/edit', ['id' => $order->getAwRmaRequestId()]);
        }
        return $result;
    }
}
