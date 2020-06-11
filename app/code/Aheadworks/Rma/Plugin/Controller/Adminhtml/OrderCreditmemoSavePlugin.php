<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Plugin\Controller\Adminhtml;

use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save;
use Aheadworks\Rma\Model\Request\Order\Storage\CurrentOrder;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class OrderCreditmemoSavePlugin
 *
 * @package Aheadworks\Rma\Plugin\Controller\Adminhtml
 */
class OrderCreditmemoSavePlugin
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
        $requestId = $subject->getRequest()->getParam('request_id', false);
        if ($requestId) {
            $result->setPath('aw_rma_admin/rma/edit', ['id' => $requestId]);
        }
        return $result;
    }
}
