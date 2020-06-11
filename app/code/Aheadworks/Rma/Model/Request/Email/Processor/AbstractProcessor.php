<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Email\Processor;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Email\EmailMetadataInterface;
use Aheadworks\Rma\Model\Email\EmailMetadataInterfaceFactory;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Rma\Model\Request\Email\UrlBuilder;
use Magento\Backend\Model\Url as BackendUrl;

/**
 * Class AbstractProcessor
 *
 * @package Aheadworks\Rma\Model\Request\Email\Processor
 */
abstract class AbstractProcessor
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var BackendUrl
     */
    protected $urlBuilderBackend;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EmailMetadataInterfaceFactory
     */
    private $emailMetadataFactory;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @param Config $config
     * @param BackendUrl $urlBuilderBackend
     * @param DataObjectFactory $dataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param EmailMetadataInterfaceFactory $emailMetadataFactory
     * @param UrlBuilder $urlBuilder
     */
    public function __construct(
        Config $config,
        BackendUrl $urlBuilderBackend,
        DataObjectFactory $dataObjectFactory,
        StoreManagerInterface $storeManager,
        EmailMetadataInterfaceFactory $emailMetadataFactory,
        UrlBuilder $urlBuilder
    ) {
        $this->config = $config;
        $this->urlBuilderBackend = $urlBuilderBackend;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->storeManager = $storeManager;
        $this->emailMetadataFactory = $emailMetadataFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Process
     *
     * @return EmailMetadataInterface
     */
    public function process()
    {
        /** @var EmailMetadataInterface $emailMetaData */
        $emailMetaData = $this->emailMetadataFactory->create();
        $emailMetaData
            ->setTemplateId($this->getTemplateId())
            ->setTemplateOptions($this->getTemplateOptions())
            ->setTemplateVariables($this->prepareTemplateVariables())
            ->setSenderName($this->getSenderName())
            ->setSenderEmail($this->getSenderEmail())
            ->setRecipientName($this->getRecipientName())
            ->setRecipientEmail($this->getRecipientEmail());

        return $emailMetaData;
    }

    /**
     * Set request
     *
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Retrieve request
     *
     * @return RequestInterface
     * @throws LocalizedException
     */
    public function getRequest()
    {
        if (!$this->request) {
            throw new LocalizedException(__('Request is not set.'));
        }
        return $this->request;
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Retrieve store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if (null === $this->storeId) {
            $this->storeId = $this->storeManager->getStore()->getId();
        }
        return $this->storeId;
    }

    /**
     * Prepare request template variables
     *
     * @return array
     */
    abstract protected function prepareRequestTemplateVariables();

    /**
     * Retrieve template id
     *
     * @return string
     */
    abstract protected function getTemplateId();

    /**
     * Retrieve recipient name
     *
     * @return string
     */
    abstract protected function getRecipientName();

    /**
     * Retrieve recipient email
     *
     * @return string
     */
    abstract protected function getRecipientEmail();

    /**
     * Prepare template variables
     *
     * @return array
     */
    protected function prepareTemplateVariables()
    {
        $requestVariables = [
            'text_id' => $this->getRequest()->getIncrementId(),
            'customer_name' => $this->getRequest()->getCustomerName(),
        ];

        $threadMessage = $this->getRequest()->getThreadMessage();
        if ($this->isThreadMessageAvailable($threadMessage)) {
            $requestVariables['notify_comment_text'] = $threadMessage->getText();
        }

        $requestVariables = array_merge($requestVariables, $this->prepareRequestTemplateVariables());
        $templateVariables = [
            'request' => $this->dataObjectFactory->create($requestVariables),
            'store' => $this->storeManager->getStore($this->getStoreId())
        ];

        return $templateVariables;
    }

    /**
     * Check if thread message is available
     *
     * @param $threadMessage
     * @return bool
     */
    protected function isThreadMessageAvailable($threadMessage)
    {
        return $threadMessage && !$threadMessage->isInternal();
    }

    /**
     * Retrieve sender name
     *
     * @return string
     */
    protected function getSenderName()
    {
        return $this->config->getDepartmentDisplayName($this->getStoreId());
    }

    /**
     * Retrieve sender email
     *
     * @return string
     */
    protected function getSenderEmail()
    {
        return $this->config->getDepartmentEmail($this->getStoreId());
    }

    /**
     * Retrieve customer RMA url
     *
     * @return string
     */
    protected function getCustomerRmaUrl()
    {
        if ($this->getRequest()->getCustomerId()) {
            $rmaLink = $this->urlBuilder->getUrl(
                'aw_rma/customer/view',
                $this->getStoreId(),
                ['id' => $this->getRequest()->getId(), '_nosid' => true]
            );
        } else {
            $rmaLink = $this->urlBuilder->getUrl(
                'aw_rma/guest/view',
                $this->getStoreId(),
                ['id' => $this->getRequest()->getExternalLink(), '_nosid' => true]
            );
        }

        return $rmaLink;
    }

    /**
     * Retrieve admin RMA url
     *
     * @return string
     */
    protected function getAdminRmaUrl()
    {
        return $this->urlBuilderBackend->getUrl('aw_rma_admin/rma/edit', ['id' => $this->getRequest()->getId()]);
    }

    /**
     * Prepare template options
     *
     * @return array
     */
    private function getTemplateOptions()
    {
        return [
            'area' => Area::AREA_FRONTEND,
            'store' => $this->getStoreId()
        ];
    }
}
