<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

/**
 * Email template interface
 * @api
 */
interface StatusEmailTemplateInterface extends StoreValueInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const CUSTOM_TEXT = 'custom_text';
    /**#@-*/

    /**
     * Get custom text
     *
     * @return string
     */
    public function getCustomText();

    /**
     * Set option value
     *
     * @param string $customText
     * @return $this
     */
    public function setCustomText($customText);
}
