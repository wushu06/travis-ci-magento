<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ThreadMessage;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Email\Sender;
use Aheadworks\Rma\Model\Request\Email\ProcessorList;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;

/**
 * Class Notifier
 *
 * @package Aheadworks\Rma\Model\ThreadMessage
 */
class Notifier
{
    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var ProcessorList
     */
    private $emailProcessorList;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Sender $sender
     * @param ProcessorList $emailProcessorList
     * @param LoggerInterface $logger
     */
    public function __construct(
        Sender $sender,
        ProcessorList $emailProcessorList,
        LoggerInterface $logger
    ) {
        $this->sender = $sender;
        $this->emailProcessorList = $emailProcessorList;
        $this->logger = $logger;
    }

    /**
     * Notify about new message
     *
     * @param RequestInterface $request
     * @param bool $causedByAdmin
     * @param int|null $storeId
     * @return bool
     */
    public function notifyAboutNewMessage($request, $causedByAdmin, $storeId = null)
    {
        $emailProcessor = $causedByAdmin
            ? ProcessorList::ADMIN_REPLY_PROCESSOR
            : ProcessorList::CUSTOMER_REPLY_PROCESSOR;

        $processor = $this->emailProcessorList->getProcessor($emailProcessor);
        $emailMetadata = $processor
            ->setRequest($request)
            ->setStoreId($storeId)
            ->process();

        try {
            $this->sender->send($emailMetadata);
        } catch (MailException $e) {
            $this->logger->critical($e);
            return false;
        }

        return true;
    }
}
