<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierPool;

/**
 * Class Modifier
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta
 */
class Modifier
{
    /**
     * @var ModifierPool
     */
    private $modifierPool;

    /**
     * @param ModifierPool $modifierPool
     */
    public function __construct(ModifierPool $modifierPool)
    {
        $this->modifierPool = $modifierPool;
    }

    /**
     * Modify attribute metadata
     *
     * @param string $attributeCode
     * @param array $metadata
     * @return array
     */
    public function modify($attributeCode, $metadata)
    {
        $modifier = $this->modifierPool->getModifier($attributeCode);
        return $modifier->modify($metadata);
    }
}
