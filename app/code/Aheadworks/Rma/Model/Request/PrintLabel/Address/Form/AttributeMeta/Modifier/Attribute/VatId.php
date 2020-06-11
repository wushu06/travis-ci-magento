<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierInterface;
use Magento\Customer\Helper\Address as AddressHelper;

/**
 * Class VatId
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute
 */
class VatId implements ModifierInterface
{
    /**
     * @var AddressHelper
     */
    private $addressHelper;

    /**
     * @param AddressHelper $addressHelper
     */
    public function __construct(
        AddressHelper $addressHelper
    ) {
        $this->addressHelper = $addressHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($metadata)
    {
        $metadata['visible'] = $this->addressHelper->isVatAttributeVisible();

        return $metadata;
    }
}
