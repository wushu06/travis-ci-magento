<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Request;

use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\ThreadMessageRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Ui\Component\Container;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class ThreadList
 *
 * @package Aheadworks\Rma\Ui\Component\Form\Request
 */
class ThreadList extends Container
{
    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var ThreadMessageRepositoryInterface
     */
    private $threadMessageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AuthSession
     */
    private $authSession;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ThreadMessageRepositoryInterface $threadMessageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Config $config
     * @param AuthSession $authSession
     * @param TimezoneInterface $localeDate
     * @param UiComponentInterface[] $components
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ThreadMessageRepositoryInterface $threadMessageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Config $config,
        AuthSession $authSession,
        TimezoneInterface $localeDate,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->uiComponentFactory = $uiComponentFactory;
        $this->threadMessageRepository = $threadMessageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->config = $config;
        $this->authSession = $authSession;
        $this->localeDate = $localeDate;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);
        if (isset($dataSource['data']['id']) && $dataSource['data']['id']) {
            $requestId = $dataSource['data']['id'];
            $threadList = [];
            foreach ($this->getThreadMessages($requestId) as $threadMessage) {
                $threadList[] = [
                    'date' => $this->getFormattedDate($threadMessage->getCreatedAt()),
                    'name' => $this->getOwnerName($dataSource['data'], $threadMessage),
                    'text' => $threadMessage->getText(),
                    'classes' => $this->getMessageClasses($threadMessage),
                    'attachments' => $this->getAttachments($requestId, $threadMessage)
                ];
            }
            $dataSource['data']['thread_list'] = $threadList;
        }

        return $dataSource;
    }

    /**
     * Retrieve request messages
     *
     * @param int $requestId
     * @return ThreadMessageInterface[]
     */
    private function getThreadMessages($requestId)
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(ThreadMessageInterface::CREATED_AT)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();
        $this->searchCriteriaBuilder
            ->addFilter(ThreadMessageInterface::REQUEST_ID, $requestId)
            ->addSortOrder($sortOrder);

        return $this->threadMessageRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
    }

    /**
     * Retrieve thread message attachments
     *
     * @param int $requestId
     * @param ThreadMessageInterface $threadMessage
     * @return array
     */
    private function getAttachments($requestId, $threadMessage)
    {
        $attachments = [];
        if (empty($threadMessage->getAttachments())) {
            return $attachments;
        }

        foreach ($threadMessage->getAttachments() as $attachment) {
            $attachments[] = [
                'name' => $attachment->getName(),
                'link' => $this->getDownloadUrl($requestId, $attachment->getFileName(), $threadMessage->getId())
            ];
        }

        return $attachments;
    }

    /**
     * Retrieve downloadable url
     *
     * @param int $requestId
     * @param string $attachmentFileName
     * @param int $messageId
     * @return string
     */
    private function getDownloadUrl($requestId, $attachmentFileName, $messageId)
    {
        $params = [
            'file' => $attachmentFileName,
            'id' => $requestId,
            'message' => $messageId
        ];

        return $this->getContext()->getUrl('*/*/download', $params);
    }

    /**
     * Retrieve thread message classes
     *
     * @param ThreadMessageInterface $threadMessage
     * @return string
     */
    private function getMessageClasses($threadMessage)
    {
        $classNames = [];
        if ($threadMessage->getOwnerType() == Owner::ADMIN) {
            $classNames[] = 'admin';
        }
        if ($threadMessage->getOwnerType() == Owner::CUSTOMER) {
            $classNames[] = 'customer';
        }
        if ($threadMessage->isAuto()) {
            $classNames[] = 'auto';
        }
        if ($threadMessage->isInternal()) {
            $classNames[] = 'internal';
        }

        return implode(' ', $classNames);
    }

    /**
     * Retrieve formatted date
     *
     * @param $date
     * @return string
     */
    private function getFormattedDate($date)
    {
        return $this->localeDate->date($date, null, true)->format('d M Y H:i:s A');
    }

    /**
     * Retrieve owner name for thread message
     *
     * @param array $request
     * @param ThreadMessageInterface $threadMessage
     * @return string
     */
    private function getOwnerName($request, $threadMessage)
    {
        $isAdmin = $threadMessage->getOwnerType() == Owner::ADMIN;
        if ($threadMessage->isAuto()) {
            $ownerName = __('Automessage');
            $ownerInfo = $this->config->getDepartmentDisplayName();
        } else {
            $ownerName = $threadMessage->getOwnerName();
            $ownerInfo =  $isAdmin ? __('Administrator') : __('Customer');
        }

        if ($isAdmin && $this->currentAdminIsOwner($threadMessage->getOwnerId())) {
            $ownerInfo .= ', me';
        }
        if ($threadMessage->getOwnerType() == Owner::CUSTOMER && empty($request['customer_id'])) {
            $ownerName = $request['customer_name'];
        }

        return $ownerName . ' (' . $ownerInfo . ')';
    }

    /**
     * Current admin user is owner
     *
     * @param int $ownerId
     * @return bool
     */
    public function currentAdminIsOwner($ownerId)
    {
        return $ownerId == $this->authSession->getUser()->getId();
    }
}
