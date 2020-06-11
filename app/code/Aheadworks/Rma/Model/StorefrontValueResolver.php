<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterface;

/**
 * Class StorefrontValueResolver
 *
 * @package Aheadworks\Rma\Model
 */
class StorefrontValueResolver
{
    /**
     * Retrieve storefront value
     *
     * @param StoreValueInterface[] $objects
     * @param int $storeId
     * @return string
     */
    public function getStorefrontValue($objects, $storeId)
    {
        return $this->getStorefrontData($objects, $storeId, true);
    }

    /**
     * Retrieve storefront email template data
     *
     * @param StatusEmailTemplateInterface[] $objects
     * @param int $storeId
     * @return StatusEmailTemplateInterface
     */
    public function getStorefrontValueEmailTemplate($objects, $storeId)
    {
        return $this->getStorefrontData($objects, $storeId, false);
    }

    /**
     * Retrieve storefront data
     *
     * @param StoreValueInterface[]|StatusEmailTemplateInterface[] $objects
     * @param int $storeId
     * @param bool $returnValue
     * @return StatusEmailTemplateInterface|string|null
     */
    private function getStorefrontData($objects, $storeId, $returnValue)
    {
        $storefrontValue = null;
        $minStoreIdStorefrontValue = null;
        $minStoreIdAvailable = null;
        foreach ($objects as $object) {
            if ($object->getStoreId() == $storeId) {
                $storefrontValue = $returnValue ? $object->getValue() : $object;
            }
            if (null === $minStoreIdAvailable) {
                $minStoreIdAvailable = $object->getStoreId();
            }
            if ($minStoreIdAvailable >= $object->getStoreId()
                && !empty($object->getValue())
            ) {
                $minStoreIdAvailable = $object->getStoreId();
                $minStoreIdStorefrontValue = $returnValue ? $object->getValue() : $object;
            }
        }

        return empty($storefrontValue) ? $minStoreIdStorefrontValue : $storefrontValue;
    }
}
