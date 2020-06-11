<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Request item interface
 * @api
 */
interface RequestItemInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const ITEM_ID = 'item_id';
    const QTY = 'qty';
    const CUSTOM_FIELDS = 'custom_fields';
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
     * Get item id
     *
     * @return int
     */
    public function getItemId();

    /**
     * Set item id
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Get qty
     *
     * @return int
     */
    public function getQty();

    /**
     * Set qty
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);

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
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Rma\Api\Data\RequestItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Rma\Api\Data\RequestItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\RequestItemExtensionInterface $extensionAttributes
    );
}
