<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\CustomField;

use Aheadworks\Rma\Model\Source\CustomField\EditAt as EditAtStatusSource;
use Aheadworks\Rma\Model\Source\Request\Status;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class CustomerEditAt
 *
 * @package Aheadworks\Rma\Model\Source\CustomField
 */
class CustomerEditAt implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var EditAtStatusSource
     */
    private $editAtStatusSource;

    /**
     * @param EditAtStatusSource $editAtStatusSource
     */
    public function __construct(
        EditAtStatusSource $editAtStatusSource
    ) {
        $this->editAtStatusSource = $editAtStatusSource;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $options = $this->editAtStatusSource->toOptionArray();
            $this->options = [];
            foreach ($options as $option) {
                if (in_array($option['value'], [Status::CLOSED, Status::CANCELED])) {
                    continue;
                }
                $this->options[] = $option;
            }
        }
        return $this->options;
    }
}
