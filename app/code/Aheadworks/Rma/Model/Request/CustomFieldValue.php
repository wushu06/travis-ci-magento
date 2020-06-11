<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class CustomFieldValue
 *
 * @package Aheadworks\Rma\Model\Request
 */
class CustomFieldValue extends AbstractSimpleObject implements RequestCustomFieldValueInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFieldId()
    {
        return $this->_get(self::FIELD_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldId($fieldId)
    {
        return $this->setData(self::FIELD_ID, $fieldId);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
