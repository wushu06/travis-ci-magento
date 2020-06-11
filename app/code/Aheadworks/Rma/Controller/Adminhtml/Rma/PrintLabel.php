<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Aheadworks\Rma\Api\RequestManagementInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class PrintLabel
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class PrintLabel extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

    /**
     * @var RequestManagementInterface
     */
    private $requestManagement;

    /**
     * @param Context $context
     * @param RequestManagementInterface $requestManagement
     */
    public function __construct(
        Context $context,
        RequestManagementInterface $requestManagement
    ) {
        parent::__construct($context);
        $this->requestManagement = $requestManagement;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $requestId = $this->getRequest()->getParam('id', false);
        if (!$requestId) {
            throw new NotFoundException(__('Page not found.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl(
            $this->requestManagement->getPrintLabelUrlForAdmin($requestId)
        );
    }
}
