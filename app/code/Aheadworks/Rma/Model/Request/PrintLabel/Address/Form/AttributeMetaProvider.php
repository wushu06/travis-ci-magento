<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Mapper;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\AvailabilityChecker;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier;
use Magento\Customer\Api\AddressMetadataInterface;

/**
 * Class AttributeMetaProvider
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form
 */
class AttributeMetaProvider
{
    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var AvailabilityChecker
     */
    private $availabilityChecker;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var Modifier
     */
    private $modifier;

    /**
     * @param AddressMetadataInterface $addressMetadata
     * @param AvailabilityChecker $availabilityChecker
     * @param Mapper $mapper
     * @param Modifier $modifier
     */
    public function __construct(
        AddressMetadataInterface $addressMetadata,
        AvailabilityChecker $availabilityChecker,
        Mapper $mapper,
        Modifier $modifier
    ) {
        $this->addressMetadata = $addressMetadata;
        $this->availabilityChecker = $availabilityChecker;
        $this->mapper = $mapper;
        $this->modifier = $modifier;
    }

    /**
     * Get address attributes metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        $result = [];
        $attributes = $this->addressMetadata->getAttributes('customer_address_edit');
        foreach ($attributes as $attributeMeta) {
            if ($this->availabilityChecker->isAvailableOnForm($attributeMeta)) {
                $attributeCode = $attributeMeta->getAttributeCode();
                $metadata = $this->mapper->map($attributeMeta);

                if (isset($metadata['label'])) {
                    $metadata['label'] = __($metadata['label']);
                }

                $result[$attributeCode] = $this->modifier->modify($attributeCode, $metadata);
            }
        }
        return $result;
    }
}
