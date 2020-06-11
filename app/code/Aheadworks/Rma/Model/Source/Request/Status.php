<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\Request;

use Magento\Framework\Option\ArrayInterface;
use Aheadworks\Rma\Model\Status\Request\StatusList;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Status
 *
 * @package Aheadworks\Rma\Model\Source\Request
 */
class Status implements ArrayInterface
{
    /**#@+
     * Constants defined for default RMA status
     */
    const APPROVED = 1;
    const CANCELED = 2;
    const CLOSED = 3;
    const ISSUE_REFUND = 4;
    const PACKAGE_RECEIVED = 5;
    const PACKAGE_SENT = 6;
    const PENDING_APPROVAL = 7;
    /**#@-*/

    /**
     * @var array
     */
    private $options;

    /**
     * @var StatusList
     */
    private $statusList;

    /**
     * @param StatusList $statusList
     */
    public function __construct(
        StatusList $statusList
    ) {
        $this->statusList = $statusList;
    }

    /**
     * Retrieve options without translation
     *
     * @return array
     * @throws LocalizedException
     */
    public function getOptionsWithoutTranslation()
    {
        $statusList = $this->statusList->retrieve();
        $options = [];
        foreach ($statusList as $status) {
            $options[] = ['value' => $status->getId(), 'label' => $status->getName()];
        }

        return $options;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            foreach ($this->getOptionsWithoutTranslation() as $option) {
                $this->options[] = ['value' => $option['value'], 'label' => __($option['label'])];
            }
        }
        return $this->options;
    }

    /**
     * Retrieve option by value
     *
     * @param int $value
     * @param bool $translate
     * @return string|null
     * @throws LocalizedException
     */
    public function getOptionLabelByValue($value, $translate = true)
    {
        $options = $translate
            ? $this->toOptionArray()
            : $this->getOptionsWithoutTranslation();

        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return null;
    }
}
