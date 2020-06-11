<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class AvailabilityChecker
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta
 */
class AvailabilityChecker
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Check if attribute is available on checkout address form
     *
     * @param AttributeMetadataInterface $attributeMeta
     * @return bool
     */
    public function isAvailableOnForm(AttributeMetadataInterface $attributeMeta)
    {
        if ($this->moduleManager->isEnabled('Magento_CustomerCustomAttributes')) {
            return true;
        }
        return !$attributeMeta->isUserDefined();
    }
}
