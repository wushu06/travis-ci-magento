<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier;

/**
 * Interface ModifierInterface
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier
 */
interface ModifierInterface
{
    /**
     * Modify attribute metadata
     *
     * @param array $metadata
     * @return array
     */
    public function modify($metadata);
}
