<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

/**
 * Custom field option action interface
 * @api
 */
interface CustomFieldOptionActionInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const TITLE = 'title';
    const OPERATION = 'operation';
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
     * Get action title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set action title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get operation
     *
     * @return string
     */
    public function getOperation();

    /**
     * Set operation
     *
     * @param string $operation
     * @return $this
     */
    public function setOperation($operation);
}
