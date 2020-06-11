<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterfaceFactory;
use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Api\ThreadMessageManagementInterface;
use Aheadworks\Rma\Model\Email\Sender;
use Aheadworks\Rma\Model\Request\Email\Processor\AdminChangedStatus;
use Aheadworks\Rma\Model\Request\Email\Processor\CustomerChangedStatus;
use Aheadworks\Rma\Model\Request\Email\ProcessorList;
use Aheadworks\Rma\Model\Source\Request\Status;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;

/**
 * Class Notifier
 *
 * @package Aheadworks\Rma\Model\Request
 */
class Notifier
{
    /**
     * @var  StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var ThreadMessageManagementInterface
     */
    private $threadMessageManagement;

    /**
     * @var ThreadMessageInterfaceFactory
     */
    private $threadMessageFactory;

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
     * @param StatusRepositoryInterface $statusRepository
     * @param ThreadMessageManagementInterface $threadMessageManagement
     * @param ThreadMessageInterfaceFactory $threadMessageFactory
     * @param Sender $sender
     * @param ProcessorList $emailProcessorList
     * @param LoggerInterface $logger
     */
    public function __construct(
        StatusRepositoryInterface $statusRepository,
        ThreadMessageManagementInterface $threadMessageManagement,
        ThreadMessageInterfaceFactory $threadMessageFactory,
        Sender $sender,
        ProcessorList $emailProcessorList,
        LoggerInterface $logger
    ) {
        $this->statusRepository = $statusRepository;
        $this->threadMessageManagement = $threadMessageManagement;
        $this->threadMessageFactory = $threadMessageFactory;
        $this->sender = $sender;
        $this->emailProcessorList = $emailProcessorList;
        $this->logger = $logger;
    }

    /**
     * Notify about status change on thread
     *
     * @param RequestInterface $request
     * @param int|null $storeId
     * @return bool
     */
    public function notifyAboutStatusChangeOnThread($request, $storeId = null)
    {
        /** @var StatusInterface $status */
        $status = $this->statusRepository->get($request->getStatusId(), $storeId);
        if ($status->isThread()) {
            /** @var ThreadMessageInterface $threadMessageObject */
            $threadMessageObject = $this->threadMessageFactory->create();
            $threadMessageObject
                ->setText($status->getStorefrontThreadTemplate())
                ->setOwnerType(Owner::ADMIN)
                ->setOwnerId(0)
                ->setIsAuto(true)
                ->setRequestId($request->getId());
            $this->threadMessageManagement->addThreadMessage($threadMessageObject);

            return true;
        }

        return false;
    }

    /**
     * Notify about status change on email
     *
     * @param RequestInterface $request
     * @param bool $causedByAdmin
     * @param int|null $storeId
     * @return bool
     */
    public function notifyAboutStatusChangeOnEmail($request, $causedByAdmin, $storeId = null)
    {
        /** @var StatusInterface $status */
        $status = $this->statusRepository->get($request->getStatusId(), $storeId);
        try {
            if ($status->isEmailCustomer()) {
                /** @var CustomerChangedStatus $processor */
                $processor = $this->emailProcessorList->getProcessor(ProcessorList::CUSTOMER_CHANGED_STATUS_PROCESSOR);
                $emailMetadata = $processor
                    ->setRequest($request)
                    ->setStatus($status)
                    ->setStoreId($storeId)
                    ->process();
                $this->sender->send($emailMetadata);
            }

            if ($status->isEmailAdmin()
                && !($request->getStatusId() == Status::CANCELED && $causedByAdmin)
            ) {
                /** @var AdminChangedStatus $processor */
                $processor = $this->emailProcessorList->getProcessor(ProcessorList::ADMIN_CHANGED_STATUS_PROCESSOR);
                $emailMetadata = $processor
                    ->setRequest($request)
                    ->setStatus($status)
                    ->setStoreId($storeId)
                    ->process();
                $this->sender->send($emailMetadata);
            }
        } catch (MailException $e) {
            $this->logger->critical($e);
            return false;
        }

        return true;
    }
}
