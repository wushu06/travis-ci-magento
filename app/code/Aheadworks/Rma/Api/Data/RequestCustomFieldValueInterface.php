<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

/**
 * Request custom field value interface
 * @api
 */
interface RequestCustomFieldValueInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const FIELD_ID = 'field_id';
    const VALUE = 'value';
    /**#@-*/

    /**
     * Get field id
     *
     * @return int
     */
    public function getFieldId();

    /**
     * Set field id
     *
     * @param int $fieldId
     * @return $this
     */
    public function setFieldId($fieldId);

    /**
     * Get value
     *
     * @return string|array
     */
    public function getValue();

    /**
     * Set value
     *
     * @param string|array $value
     * @return $this
     */
    public function setValue($value);
}
