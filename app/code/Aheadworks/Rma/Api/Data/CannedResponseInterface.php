<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Canned response interface
 * @api
 */
interface CannedResponseInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const TITLE = 'title';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const IS_ACTIVE = 'is_active';
    const STORE_RESPONSE_VALUES = 'store_response_values';
    const RESPONSE_TEXT = 'response_text';
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
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

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
     * Is active
     *
     * @return bool
     */
    public function isActive();

    /**
     * Set is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get response store values
     *
     * @return \Aheadworks\Rma\Api\Data\StoreValueInterface[]
     */
    public function getStoreResponseValues();

    /**
     * Set response store values
     *
     * @param \Aheadworks\Rma\Api\Data\StoreValueInterface[] $responseValues
     * @return $this
     */
    public function setStoreResponseValues($responseValues);

    /**
     * Get response text
     *
     * @return string
     */
    public function getResponseText();

    /**
     * Set response text
     *
     * @param string $responseText
     * @return $this
     */
    public function setResponseText($responseText);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Rma\Api\Data\CannedResponseExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Rma\Api\Data\CannedResponseExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\CannedResponseExtensionInterface $extensionAttributes
    );
}
