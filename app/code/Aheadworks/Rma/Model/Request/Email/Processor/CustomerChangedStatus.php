<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Email\Processor;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Aheadworks\Rma\Model\Request\Email\UrlBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Rma\Model\Email\EmailMetadataInterfaceFactory;
use Magento\Backend\Model\Url as BackendUrl;

/**
 * Class CustomerChangedStatus
 *
 * @package Aheadworks\Rma\Model\Request\Email\Processor
 */
class CustomerChangedStatus extends AbstractProcessor
{
    /**
     * @var StatusRepositoryInterface
     */
    protected $statusRepository;

    /**
     * @var StatusInterface
     */
    private $status;

    /**
     * @param Config $config
     * @param BackendUrl $urlBuilderBackend
     * @param DataObjectFactory $dataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param EmailMetadataInterfaceFactory $emailMetadataFactory
     * @param UrlBuilder $urlBuilder
     * @param StatusRepositoryInterface $statusRepository
     */
    public function __construct(
        Config $config,
        BackendUrl $urlBuilderBackend,
        DataObjectFactory $dataObjectFactory,
        StoreManagerInterface $storeManager,
        EmailMetadataInterfaceFactory $emailMetadataFactory,
        UrlBuilder $urlBuilder,
        StatusRepositoryInterface $statusRepository
    ) {
        parent::__construct(
            $config,
            $urlBuilderBackend,
            $dataObjectFactory,
            $storeManager,
            $emailMetadataFactory,
            $urlBuilder
        );
        $this->statusRepository = $statusRepository;
    }

    /**
     * Set status
     *
     * @param StatusInterface $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return StatusInterface
     */
    public function getStatus()
    {
        if (!$this->status) {
            $this->status = $this->statusRepository->get($this->getRequest()->getStatusId(), $this->getStoreId());
        }
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareRequestTemplateVariables()
    {
        $requestVariables = [
            'status' => $this->getStatus()->getStorefrontLabel(),
            'url' => $this->getCustomerRmaUrl(),
            'notify_rma_address' => $this->config->getDepartmentAddress($this->getStoreId()),
            'custom_text' => $this->getStatus()->getStorefrontCustomerTemplate()->getCustomText()
        ];
        $sameRequestTemplateVariables = $this->prepareSameRequestTemplateVariables();

        return array_merge($requestVariables, $sameRequestTemplateVariables);
    }

    /**
     * Prepare same request template variables
     *
     * @return array
     */
    protected function prepareSameRequestTemplateVariables()
    {
        $requestVariables = [];
        if ($this->getRequest()->getThreadMessage()) {
            $threadMessage = $this->getRequest()->getThreadMessage();
            $requestVariables['notify_comment_label'] = $this->resolveCommentLabel($threadMessage);
        }

        return $requestVariables;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplateId()
    {
        return $this->getStatus()->getStorefrontCustomerTemplate()->getValue();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecipientName()
    {
        return $this->getRequest()->getCustomerName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecipientEmail()
    {
        return $this->getRequest()->getCustomerEmail();
    }

    /**
     * Resolve comment label
     *
     * @param ThreadMessageInterface $threadMessage
     * @return string
     */
    protected function resolveCommentLabel($threadMessage)
    {
        $label = $threadMessage->getOwnerType() == Owner::CUSTOMER
            ? __('Your comment:')
            : __('Comment from %1:', $this->config->getDepartmentDisplayName());

        return $label;
    }
}
