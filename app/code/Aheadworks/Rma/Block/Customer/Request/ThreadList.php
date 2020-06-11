<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request;

use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\ThreadMessageRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class ThreadList
 *
 * @method int getRequestId()
 * @method ThreadList setRequestId(int $requestId)
 * @method int|string getRequestIdentityValue()
 * @method ThreadList setRequestIdentityValue(int|string $requestIdentityValue)
 * @package Aheadworks\Rma\Block\Customer\Request
 */
class ThreadList extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/thread/list.phtml';

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var ThreadMessageRepositoryInterface
     */
    private $threadMessageRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestRepositoryInterface $requestRepository
     * @param ThreadMessageRepositoryInterface $threadMessageRepository
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestRepositoryInterface $requestRepository,
        ThreadMessageRepositoryInterface $threadMessageRepository,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->requestRepository = $requestRepository;
        $this->threadMessageRepository = $threadMessageRepository;
        $this->config = $config;
    }

    /**
     * Retrieve RMA Department display name
     *
     * @return string
     */
    public function getDepartmentName()
    {
        return $this->config->getDepartmentDisplayName();
    }

    /**
     * Retrieve request messages
     *
     * @return ThreadMessageInterface[]
     */
    public function getThreadMessages()
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(ThreadMessageInterface::CREATED_AT)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();
        $this->searchCriteriaBuilder
            ->addFilter(ThreadMessageInterface::REQUEST_ID, $this->getRequestId())
            ->addFilter(ThreadMessageInterface::IS_INTERNAL, 0)
            ->addSortOrder($sortOrder);

        return $this->threadMessageRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
    }

    /**
     * Retrieve thread message classes
     *
     * @param ThreadMessageInterface $threadMessage
     * @return string
     */
    public function getThreadMessageClasses($threadMessage)
    {
        $classNames = ['aw-rma-view__thread-message'];
        if ($threadMessage->getOwnerType() == Owner::ADMIN) {
            $classNames[] = 'admin';
        }
        if ($threadMessage->getOwnerType() == Owner::CUSTOMER) {
            $classNames[] = 'customer';
        }
        if ($threadMessage->isAuto()) {
            $classNames[] = 'auto';
        }

        return implode(' ', $classNames);
    }

    /**
     * Retrieve owner name for thread message
     *
     * @param ThreadMessageInterface $threadMessage
     * @return string
     */
    public function getOwnerNameForThreadMessage($threadMessage)
    {
        $type = $threadMessage->getOwnerType() == Owner::ADMIN
            ? $this->getDepartmentName()
            : 'me';

        return $threadMessage->getOwnerName() . ' (' . $type . '), ';
    }

    /**
     * Retrieve downloadable url
     *
     * @param string $attachmentFileName
     * @param int $messageId
     * @return string
     */
    public function getDownloadUrl($attachmentFileName, $messageId)
    {
        $params = [
            'file' => $attachmentFileName,
            'id' => $this->getRequestIdentityValue(),
            'message' => $messageId
        ];

        return $this->getUrl('*/*/download', $params);
    }
}
