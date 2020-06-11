<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status;

use Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface;
use Aheadworks\Rma\Model\StoreValue;

/**
 * Class EmailTemplate
 *
 * @package Aheadworks\Rma\Model\Status
 */
class EmailTemplate extends StoreValue implements StatusEmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCustomText()
    {
        return $this->_get(self::CUSTOM_TEXT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomText($customText)
    {
        return $this->setData(self::CUSTOM_TEXT, $customText);
    }
}
