<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status;

/**
 * Interface RestrictionsInterface
 *
 * @package Aheadworks\Rma\Model\Status
 */
interface RestrictionsInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const AVAILABLE_FOR_STATUSES = 'available_for_statuses';
    const AVAILABLE_FOR_ACTIONS = 'available_for_actions';
    /**#@-*/

    /**
     * Get available for statuses
     *
     * @return array
     */
    public function getAvailableForStatuses();

    /**
     * Get available for actions
     *
     * @return array
     */
    public function getAvailableForActions();

    /**
     * Set available for actions
     *
     * @param array $actions
     * @return array
     */
    public function setAvailableForActions($actions);
}
