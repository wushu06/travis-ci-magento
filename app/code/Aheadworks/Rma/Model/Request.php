<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Request\IncrementIdGenerator;
use Magento\Framework\Model\AbstractModel;
use Aheadworks\Rma\Model\Request\Validator as RequestValidator;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Aheadworks\Rma\Model\ResourceModel\Request as ResourceRequest;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Request
 *
 * @package Aheadworks\Rma\Model
 */
class Request extends AbstractModel implements RequestInterface
{
    /**
     * @var RequestValidator
     */
    private $validator;

    /**
     * @var IncrementIdGenerator
     */
    private $incrementIdGenerator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param RequestValidator $validator
     * @param IncrementIdGenerator $incrementIdGenerator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RequestValidator $validator,
        IncrementIdGenerator $incrementIdGenerator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->validator = $validator;
        $this->incrementIdGenerator = $incrementIdGenerator;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceRequest::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastReplyBy()
    {
        return $this->getData(self::LAST_REPLY_BY);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastReplyBy($lastReplyBy)
    {
        return $this->setData(self::LAST_REPLY_BY, $lastReplyBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusId()
    {
        return $this->getData(self::STATUS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusId($statusId)
    {
        return $this->setData(self::STATUS_ID, $statusId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerName()
    {
        return $this->getData(self::CUSTOMER_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrintLabel()
    {
        return $this->getData(self::PRINT_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrintLabel($printLabel)
    {
        return $this->setData(self::PRINT_LABEL, $printLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function getExternalLink()
    {
        return $this->getData(self::EXTERNAL_LINK);
    }

    /**
     * {@inheritdoc}
     */
    public function setExternalLink($externalLink)
    {
        return $this->setData(self::EXTERNAL_LINK, $externalLink);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomFields()
    {
        return $this->getData(self::CUSTOM_FIELDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomFields($customFields)
    {
        return $this->setData(self::CUSTOM_FIELDS, $customFields);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderItems()
    {
        return $this->getData(self::ORDER_ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderItems($orderItems)
    {
        return $this->setData(self::ORDER_ITEMS, $orderItems);
    }

    /**
     * {@inheritdoc}
     */
    public function getThreadMessage()
    {
        return $this->getData(self::THREAD_MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setThreadMessage($threadMessage)
    {
        return $this->setData(self::THREAD_MESSAGE, $threadMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\RequestExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        $now = new \DateTime();
        $now = $now->format(DateTime::DATETIME_PHP_FORMAT);
        if (!$this->getId()) {
            $this
                ->setCreatedAt($now)
                ->setIncrementId($this->incrementIdGenerator->generate());
        }
        $this->setUpdatedAt($now);
        $this->validateBeforeSave();
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
