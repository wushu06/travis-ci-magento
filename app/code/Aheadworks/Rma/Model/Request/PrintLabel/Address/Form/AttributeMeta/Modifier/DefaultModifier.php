<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier;

/**
 * Class DefaultModifier
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier
 */
class DefaultModifier implements ModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public function modify($metadata)
    {
        return $metadata;
    }
}
