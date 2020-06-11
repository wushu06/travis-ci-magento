<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Email\Processor;

use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Aheadworks\Rma\Model\Request\Email\UrlBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Backend\Model\Url as BackendUrl;
use Aheadworks\Rma\Model\Email\EmailMetadataInterfaceFactory;
use Aheadworks\Rma\Model\Request\Resolver\Order as OrderResolver;

/**
 * Class AdminChangedStatus
 *
 * @package Aheadworks\Rma\Model\Request\Email\Processor
 */
class AdminChangedStatus extends CustomerChangedStatus
{
    /**
     * @var OrderResolver
     */
    private $orderResolver;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param Config $config
     * @param BackendUrl $urlBuilderBackend
     * @param DataObjectFactory $dataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param EmailMetadataInterfaceFactory $emailMetadataFactory
     * @param UrlBuilder $urlBuilder
     * @param StatusRepositoryInterface $statusRepository
     * @param OrderResolver $orderResolver
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        Config $config,
        BackendUrl $urlBuilderBackend,
        DataObjectFactory $dataObjectFactory,
        StoreManagerInterface $storeManager,
        EmailMetadataInterfaceFactory $emailMetadataFactory,
        UrlBuilder $urlBuilder,
        StatusRepositoryInterface $statusRepository,
        OrderResolver $orderResolver,
        TimezoneInterface $localeDate
    ) {
        parent::__construct(
            $config,
            $urlBuilderBackend,
            $dataObjectFactory,
            $storeManager,
            $emailMetadataFactory,
            $urlBuilder,
            $statusRepository
        );
        $this->orderResolver = $orderResolver;
        $this->localeDate = $localeDate;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareRequestTemplateVariables()
    {
        $requestVariables = [
            'customer_email'          => $this->getRequest()->getCustomerEmail(),
            'status'                  => $this->getStatus()->getStorefrontLabel(),
            'custom_fields'           => $this->getRequest()->getCustomFields(),
            'custom_text'             => $this->getStatus()->getStorefrontAdminTemplate()->getCustomText(),
            'items'                   => $this->getRequest()->getOrderItems(),
            'formatted_created_at'    => $this->formatDate($this->getRequest()->getCreatedAt(), $this->getStoreId()),
            'admin_url'               => $this->getAdminRmaUrl(),
            'order_id'                => $this->orderResolver->getIncrementId($this->getRequest()->getOrderId()),
            'notify_order_admin_link' => $this->getOrderUrl($this->getRequest()->getOrderId())
        ];
        $sameRequestTemplateVariables = $this->prepareSameRequestTemplateVariables();

        return array_merge($requestVariables, $sameRequestTemplateVariables);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplateId()
    {
        return $this->getStatus()->getStorefrontAdminTemplate()->getValue();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecipientName()
    {
        return $this->getSenderName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecipientEmail()
    {
        return $this->getSenderEmail();
    }

    /**
     * Resolve comment label
     *
     * @param ThreadMessageInterface $threadMessage
     * @return string
     */
    protected function resolveCommentLabel($threadMessage)
    {
        $label = $threadMessage->getOwnerType() == Owner::ADMIN
            ? __('Your comment:')
            : __('Comment from customer:');

        return $label;
    }

    /**
     * Format date
     *
     * @param string $date
     * @param int $storeId
     * @return string
     */
    private function formatDate($date, $storeId)
    {
        $date = new \DateTime($date);

        return $this->localeDate->formatDateTime($date, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, $storeId);
    }

    /**
     * Retrieve order url
     *
     * @param int $orderId
     * @return string
     */
    private function getOrderUrl($orderId)
    {
        return $this->urlBuilderBackend->getUrl('sales/order/view', ['order_id' => $orderId]);
    }
}
