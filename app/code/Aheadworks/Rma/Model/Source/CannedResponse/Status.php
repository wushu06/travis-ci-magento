<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\CannedResponse;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\Rma\Model\Source\CannedResponse
 */
class Status implements OptionSourceInterface
{
    /**#@+
     * Canned response's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Prepare canned response's statuses
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Prepare array with options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_ENABLED,  'label' => __('Enabled')],
            ['value' => self::STATUS_DISABLED,  'label' => __('Disabled')],
        ];
    }

    /**
     * Prepare array with options for mass action
     *
     * @return array
     */
    public function toOptionArrayForMassStatus()
    {
        return [
            ['value' => self::STATUS_ENABLED,  'label' => __('Enable')],
            ['value' => self::STATUS_DISABLED,  'label' => __('Disable')],
        ];
    }
}
