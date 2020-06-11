<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Guest;

use Aheadworks\Rma\Api\RequestManagementInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Controller\GuestAction;
use Magento\Framework\App\Action\Context;
use Aheadworks\Rma\Model\Config;

/**
 * Class PrintLabel
 *
 * @package Aheadworks\Rma\Controller\Guest
 */
class PrintLabel extends GuestAction
{
    /**
     * @var RequestManagementInterface
     */
    private $requestManagement;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param Config $config
     * @param RequestManagementInterface $requestManagement
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        Config $config,
        RequestManagementInterface $requestManagement
    ) {
        parent::__construct($context, $requestRepository, $config);
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
