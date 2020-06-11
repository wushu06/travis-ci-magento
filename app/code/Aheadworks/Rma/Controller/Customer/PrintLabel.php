<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Customer;

use Aheadworks\Rma\Api\RequestManagementInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Controller\CustomerAction;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class PrintLabel
 *
 * @package Aheadworks\Rma\Controller\Customer
 */
class PrintLabel extends CustomerAction
{
    /**
     * @var RequestManagementInterface
     */
    private $requestManagement;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param CustomerSession $customerSession
     * @param RequestManagementInterface $requestManagement
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        CustomerSession $customerSession,
        RequestManagementInterface $requestManagement
    ) {
        parent::__construct($context, $requestRepository, $customerSession);
        $this->requestManagement = $requestManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setUrl(
            $this->requestManagement->getPrintLabelUrl($this->getRmaRequest()->getId())
        );
    }
}
