<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierInterface;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Region as RegionSource;

/**
 * Class RegionId
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute
 */
class RegionId implements ModifierInterface
{
    /**
     * @var RegionSource
     */
    private $regionSource;

    /**
     * @param RegionSource $regionSource
     */
    public function __construct(
        RegionSource $regionSource
    ) {
        $this->regionSource = $regionSource;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($metadata)
    {
        $metadata['options'] = $this->regionSource->getAllOptions();
        return $metadata;
    }
}
