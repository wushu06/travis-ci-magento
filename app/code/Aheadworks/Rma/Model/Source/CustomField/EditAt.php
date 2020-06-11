<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\CustomField;

use Aheadworks\Rma\Model\Source\Request\Status as RequestStatusSource;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class EditAt
 *
 * @package Aheadworks\Rma\Model\Source\CustomField
 */
class EditAt implements ArrayInterface
{
    /**
     * @var string
     */
    const NEW_REQUEST_PAGE = -1;

    /**
     * @var array
     */
    private $options;

    /**
     * @var RequestStatusSource
     */
    private $requestStatusSource;

    /**
     * @param RequestStatusSource $requestStatusSource
     */
    public function __construct(
        RequestStatusSource $requestStatusSource
    ) {
        $this->requestStatusSource = $requestStatusSource;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $this->options = [
                ['value' => self::NEW_REQUEST_PAGE, 'label' => __('New Request Page')]
            ];
            $this->options = array_merge($this->options, $this->requestStatusSource->toOptionArray());
        }
        return $this->options;
    }
}
