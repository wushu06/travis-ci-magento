<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma\Action;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Aheadworks\Rma\Model\Request\Order\Replacement;
use Magento\Backend\Model\Session\Quote as BackendQuote;

/**
 * Class Replace
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma\Action
 */
class Replace extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

    /**
     * @var Replacement
     */
    private $orderReplacement;

    /**
     * @var BackendQuote
     */
    private $backendQuote;

    /**
     * @param Context $context
     * @param Replacement $orderReplacement
     * @param BackendQuote $backendQuote
     */
    public function __construct(
        Context $context,
        Replacement $orderReplacement,
        BackendQuote $backendQuote
    ) {
        parent::__construct($context);
        $this->orderReplacement = $orderReplacement;
        $this->backendQuote = $backendQuote;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $requestId = $this->getRequest()->getParam('request_id');
        $this->backendQuote->clearStorage();
        $this->backendQuote->setUseOldShippingMethod(true);
        $this->orderReplacement->prepare($requestId);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order_create');
        return $resultRedirect;
    }
}
