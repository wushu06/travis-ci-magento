<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Magento\Framework\Model\AbstractModel;
use Aheadworks\Rma\Model\ResourceModel\ThreadMessage as ResourceThreadMessage;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class ThreadMessage
 *
 * @package Aheadworks\Rma\Model
 */
class ThreadMessage extends AbstractModel implements ThreadMessageInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceThreadMessage::class);
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
    public function getRequestId()
    {
        return $this->getData(self::REQUEST_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestId($requestId)
    {
        return $this->setData(self::REQUEST_ID, $requestId);
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
    public function getText()
    {
        return $this->getData(self::TEXT);
    }

    /**
     * {@inheritdoc}
     */
    public function setText($text)
    {
        return $this->setData(self::TEXT, $text);
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerType()
    {
        return $this->getData(self::OWNER_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerType($ownerType)
    {
        return $this->setData(self::OWNER_TYPE, $ownerType);
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerName()
    {
        return $this->getData(self::OWNER_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerName($ownerName)
    {
        return $this->setData(self::OWNER_NAME, $ownerName);
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return $this->getData(self::OWNER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerId($ownerId)
    {
        return $this->setData(self::OWNER_ID, $ownerId);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuto()
    {
        return $this->getData(self::IS_AUTO);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAuto($isAuto)
    {
        return $this->setData(self::IS_AUTO, $isAuto);
    }

    /**
     * {@inheritdoc}
     */
    public function isInternal()
    {
        return $this->getData(self::IS_INTERNAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInternal($isInternal)
    {
        return $this->setData(self::IS_INTERNAL, $isInternal);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments()
    {
        return $this->getData(self::ATTACHMENTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttachments($attachments)
    {
        return $this->setData(self::ATTACHMENTS, $attachments);
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
        \Aheadworks\Rma\Api\Data\ThreadMessageExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        if (!$this->getId()) {
            $now = new \DateTime();
            $now = $now->format(DateTime::DATETIME_PHP_FORMAT);
            $this->setCreatedAt($now);
        }
    }
}
