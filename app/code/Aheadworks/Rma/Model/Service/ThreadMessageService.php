<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Service;

use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Api\ThreadMessageManagementInterface;
use Aheadworks\Rma\Api\ThreadMessageRepositoryInterface;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Aheadworks\Rma\Model\ThreadMessage\Notifier as ThreadMessageNotifier;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ThreadMessageService
 *
 * @package Aheadworks\Rma\Model\Service
 */
class ThreadMessageService implements ThreadMessageManagementInterface
{
    /**
     * @var ThreadMessageRepositoryInterface
     */
    private $threadMessageRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ThreadMessageNotifier
     */
    private $threadMessageNotifier;

    /**
     * @param ThreadMessageRepositoryInterface $threadMessageRepository
     * @param RequestRepositoryInterface $requestRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ThreadMessageNotifier $threadMessageNotifier
     */
    public function __construct(
        ThreadMessageRepositoryInterface $threadMessageRepository,
        RequestRepositoryInterface $requestRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ThreadMessageNotifier $threadMessageNotifier
    ) {
        $this->threadMessageRepository = $threadMessageRepository;
        $this->requestRepository = $requestRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->threadMessageNotifier = $threadMessageNotifier;
    }

    /**
     * {@inheritdoc}
     */
    public function addThreadMessage(ThreadMessageInterface $threadMessage, $notify = false, $storeId = null)
    {
        try {
            $threadMessage = $this->threadMessageRepository->save($threadMessage);
            if ($notify) {
                $request = $this->requestRepository->get($threadMessage->getRequestId());
                $request->setThreadMessage($threadMessage);
                $causedByAdmin = $threadMessage->getOwnerType() == Owner::ADMIN;

                if (!$threadMessage->isInternal()) {
                    $this->threadMessageNotifier->notifyAboutNewMessage($request, $causedByAdmin);
                }
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not post new message.'), $e);
        }

        return $threadMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachment($fileName, $messageId, $requestId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ThreadMessageInterface::ID, $messageId)
            ->addFilter(ThreadMessageInterface::REQUEST_ID, $requestId)
            ->addFilter(ThreadMessageAttachmentInterface::FILE_NAME, $fileName);

        $messages = $this->threadMessageRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $message = array_shift($messages);

        if (empty($message)) {
            throw new LocalizedException(__('File not found.'));
        }

        foreach ($message->getAttachments() as $attachment) {
            if ($attachment->getFileName() == $fileName) {
                return $attachment;
            }
        }
        throw new LocalizedException(__('File not found.'));
    }
}
