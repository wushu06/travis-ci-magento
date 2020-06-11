<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute\CountryId;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute\RegionId;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute\VatId;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ModifierPool
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier
 */
class ModifierPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $modifiers = [
        'country_id' => CountryId::class,
        'prefix' => 'Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute\Prefix',
        'suffix' => 'Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute\Suffix',
        'region_id' => RegionId::class,
        'vat_id' => VatId::class
    ];

    /**
     * @var ModifierInterface[]
     */
    private $modifierInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $modifiers
     */
    public function __construct(ObjectManagerInterface $objectManager, $modifiers = [])
    {
        $this->objectManager = $objectManager;
        $this->modifiers = array_merge($this->modifiers, $modifiers);
    }

    /**
     * Get modifier
     *
     * @param string $attributeCode
     * @return ModifierInterface|null
     */
    public function getModifier($attributeCode)
    {
        if (!isset($this->modifierInstances[$attributeCode])) {
            $className = isset($this->modifiers[$attributeCode])
                ? $this->modifiers[$attributeCode]
                : DefaultModifier::class;
            $this->modifierInstances[$attributeCode] = $this->objectManager->create($className);
        }
        return $this->modifierInstances[$attributeCode];
    }
}
