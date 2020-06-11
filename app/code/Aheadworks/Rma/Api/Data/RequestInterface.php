<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Request interface
 * @api
 */
interface RequestInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const INCREMENT_ID = 'increment_id';
    const ORDER_ID = 'order_id';
    const PAYMENT_METHOD = 'payment_method';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STORE_ID = 'store_id';
    const LAST_REPLY_BY = 'last_reply_by';
    const STATUS_ID = 'status_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_NAME = 'customer_name';
    const CUSTOMER_EMAIL = 'customer_email';
    const PRINT_LABEL = 'print_label';
    const EXTERNAL_LINK = 'external_link';
    const CUSTOM_FIELDS = 'custom_fields';
    const ORDER_ITEMS = 'order_items';
    const THREAD_MESSAGE = 'thread_message';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get increment ID
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Set increment ID
     *
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * Get order id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set order id
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod();

    /**
     * Set payment method
     *
     * @param string|null $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int|null $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get last reply by
     *
     * @return int
     */
    public function getLastReplyBy();

    /**
     * Set last reply by
     *
     * @param int|null $lastReplyBy
     * @return $this
     */
    public function setLastReplyBy($lastReplyBy);

    /**
     * Get status id
     *
     * @return int
     */
    public function getStatusId();

    /**
     * Set status id
     *
     * @param int|null $statusId
     * @return $this
     */
    public function setStatusId($statusId);

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get customer name
     *
     * @return string
     */
    public function getCustomerName();

    /**
     * Set customer name
     *
     * @param string|null $customerName
     * @return $this
     */
    public function setCustomerName($customerName);

    /**
     * Get customer email
     *
     * @return string
     */
    public function getCustomerEmail();

    /**
     * Set customer email
     *
     * @param string|null $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Get print label
     *
     * @return \Aheadworks\Rma\Api\Data\RequestPrintLabelInterface
     */
    public function getPrintLabel();

    /**
     * Set print label
     *
     * @param \Aheadworks\Rma\Api\Data\RequestPrintLabelInterface $printLabel
     * @return $this
     */
    public function setPrintLabel($printLabel);

    /**
     * Get external link
     *
     * @return string
     */
    public function getExternalLink();

    /**
     * Set external link
     *
     * @param string|null $externalLink
     * @return $this
     */
    public function setExternalLink($externalLink);

    /**
     * Get custom fields
     *
     * @return \Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface[]
     */
    public function getCustomFields();

    /**
     * Set custom fields
     *
     * @param \Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface[] $customFields
     * @return $this
     */
    public function setCustomFields($customFields);

    /**
     * Get order items
     *
     * @return \Aheadworks\Rma\Api\Data\RequestItemInterface[]
     */
    public function getOrderItems();

    /**
     * Set order items
     *
     * @param \Aheadworks\Rma\Api\Data\RequestItemInterface[] $orderItems
     * @return $this
     */
    public function setOrderItems($orderItems);

    /**
     * Get thread message
     *
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageInterface
     */
    public function getThreadMessage();

    /**
     * Set thread message
     *
     * @param \Aheadworks\Rma\Api\Data\ThreadMessageInterface|null $threadMessage
     * @return $this
     */
    public function setThreadMessage($threadMessage);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Rma\Api\Data\RequestExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Rma\Api\Data\RequestExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\RequestExtensionInterface $extensionAttributes
    );
}
