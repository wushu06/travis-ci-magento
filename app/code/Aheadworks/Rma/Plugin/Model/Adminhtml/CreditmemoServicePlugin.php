<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Plugin\Model\Adminhtml;

use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Rma\Api\Data\CreditmemoInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Model\ThreadMessage\Action\MessageService;

/**
 * Class CreditmemoServicePlugin
 *
 * @package Aheadworks\Rma\Plugin\Model\Adminhtml
 */
class CreditmemoServicePlugin
{
    /**
     * RequestInterface
     */
    private $request;

    /**
     * @var MessageService
     */
    private $messageService;

    /**
     * @param RequestInterface $request
     * @param MessageService $messageService
     */
    public function __construct(
        RequestInterface $request,
        MessageService $messageService
    ) {
        $this->request = $request;
        $this->messageService = $messageService;
    }

    /**
     * Set RMA request ID to creditmemo
     *
     * @param CreditmemoManagementInterface $subject
     * @param CreditmemoInterface $creditmemo
     * @param bool $offlineRequested
     * @return array
     */
    public function beforeRefund($subject, $creditmemo, $offlineRequested)
    {
        $requestId = $this->request->getParam('request_id', false);
        if ($requestId) {
            $creditmemo->setAwRmaRequestId($requestId);
        }
        return [$creditmemo, $offlineRequested];
    }

    /**
     * Add thread message about new credit memo
     *
     * @param CreditmemoManagementInterface $subject
     * @param CreditmemoInterface $creditmemo
     * @return CreditmemoInterface
     * @throws LocalizedException
     */
    public function afterRefund($subject, $creditmemo)
    {
        $requestId = $creditmemo->getAwRmaRequestId();
        if ($requestId) {
            $this->messageService->addNewCreditmemoMessage($requestId, $creditmemo);
        }

        return $creditmemo;
    }
}
