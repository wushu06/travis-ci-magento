<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Thread message interface
 * @api
 */
interface ThreadMessageInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const REQUEST_ID = 'request_id';
    const CREATED_AT = 'created_at';
    const TEXT = 'text';
    const OWNER_TYPE = 'owner_type';
    const OWNER_NAME = 'owner_name';
    const OWNER_ID = 'owner_id';
    const IS_AUTO = 'is_auto';
    const IS_INTERNAL = 'is_internal';
    const ATTACHMENTS = 'attachments';
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
     * Get request id
     *
     * @return int
     */
    public function getRequestId();

    /**
     * Set request id
     *
     * @param int $requestId
     * @return $this
     */
    public function setRequestId($requestId);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get text
     *
     * @return string
     */
    public function getText();

    /**
     * Set text
     *
     * @param string $text
     * @return $this
     */
    public function setText($text);

    /**
     * Get owner type
     *
     * @return int
     */
    public function getOwnerType();

    /**
     * Set owner type
     *
     * @param int $ownerType
     * @return $this
     */
    public function setOwnerType($ownerType);

    /**
     * Get owner name
     *
     * @return string
     */
    public function getOwnerName();

    /**
     * Set owner name
     *
     * @param string $ownerName
     * @return $this
     */
    public function setOwnerName($ownerName);

    /**
     * Get owner id
     *
     * @return int
     */
    public function getOwnerId();

    /**
     * Set owner id
     *
     * @param int $ownerId
     * @return $this
     */
    public function setOwnerId($ownerId);

    /**
     * Check is auto
     *
     * @return bool
     */
    public function isAuto();

    /**
     * Set is auto
     *
     * @param bool $isAuto
     * @return $this
     */
    public function setIsAuto($isAuto);

    /**
     * Check is internal
     *
     * @return bool
     */
    public function isInternal();

    /**
     * Set is internal
     *
     * @param bool $isInternal
     * @return $this
     */
    public function setIsInternal($isInternal);

    /**
     * Get attachments
     *
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface[]
     */
    public function getAttachments();

    /**
     * Set attachments
     *
     * @param \Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface[] $attachments
     * @return $this
     */
    public function setAttachments($attachments);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Rma\Api\Data\ThreadMessageExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\ThreadMessageExtensionInterface $extensionAttributes
    );
}
