<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Plugin\Block\Adminhtml;

use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create as CreditmemoCreate;

/**
 * Class CreditMemoBackButtonPlugin
 *
 * @package Aheadworks\Rma\Plugin\Block\Adminhtml
 */
class CreditMemoBackButtonPlugin
{
    /**
     * Change URL to return back to rma request page if required
     *
     * @param CreditmemoCreate $subject
     * @param string $result
     * @return string
     */
    public function afterGetBackUrl($subject, $result)
    {
        $requestId = $subject->getRequest()->getParam('request_id', false);
        if ($requestId) {
            $result = $subject->getUrl('aw_rma_admin/rma/edit', ['id' => $requestId]);
        }
        return $result;
    }
}
