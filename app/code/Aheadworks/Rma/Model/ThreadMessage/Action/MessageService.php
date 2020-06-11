<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ThreadMessage\Action;

use Magento\Framework\Phrase;
use Aheadworks\Rma\Api\Data\CreditmemoInterface;
use Aheadworks\Rma\Api\Data\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterfaceFactory;
use Aheadworks\Rma\Api\ThreadMessageManagementInterface;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;

/**
 * Class MessageService
 *
 * @package Aheadworks\Rma\Model\ThreadMessage\Action
 */
class MessageService
{
    /**
     * @var ThreadMessageInterfaceFactory
     */
    private $threadMessageFactory;

    /**
     * @var ThreadMessageManagementInterface
     */
    private $threadMessageManagement;

    /**
     * @param ThreadMessageInterfaceFactory $threadMessageFactory
     * @param ThreadMessageManagementInterface $threadMessageManagement
     */
    public function __construct(
        ThreadMessageInterfaceFactory $threadMessageFactory,
        ThreadMessageManagementInterface $threadMessageManagement
    ) {
        $this->threadMessageFactory = $threadMessageFactory;
        $this->threadMessageManagement = $threadMessageManagement;
    }

    /**
     * Add message about new replacement order
     *
     * @param int $requestId
     * @param OrderInterface $order
     * @throws LocalizedException
     */
    public function addNewReplacementOrderMessage($requestId, $order)
    {
        $text = __('Replacement order #%1 created.', $order->getIncrementId());
        $this->addThreadMessage($requestId, $text);
    }

    /**
     * Add message about new credit memo
     *
     * @param int $requestId
     * @param CreditmemoInterface $creditmemo
     * @throws LocalizedException
     */
    public function addNewCreditmemoMessage($requestId, $creditmemo)
    {
        $text = __(
            'Credit Memo #%1 created for order #%2.',
            $creditmemo->getIncrementId(),
            $creditmemo->getOrder()->getIncrementId()
        );
        $this->addThreadMessage($requestId, $text);
    }

    /**
     * Add thread message
     *
     * @param int $requestId
     * @param Phrase $text
     * @throws LocalizedException
     */
    private function addThreadMessage($requestId, $text)
    {
        /** @var ThreadMessageInterface $threadMessageObject */
        $threadMessageObject = $this->threadMessageFactory->create();
        $threadMessageObject
            ->setText($text)
            ->setOwnerType(Owner::ADMIN)
            ->setOwnerId(0)
            ->setIsAuto(true)
            ->setRequestId($requestId);
        $this->threadMessageManagement->addThreadMessage($threadMessageObject, false);
    }
}
