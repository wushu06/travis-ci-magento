<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ThirdPartyModule;

use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Manager
 *
 * @package Aheadworks\Rma\Model\ThirdPartyModule
 */
class Manager
{
    /**
     * Aheadworks Coupon Code Generator module name
     */
    const CCG_MODULE_NAME = 'Aheadworks_Coupongenerator';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Check if Aheadworks Coupon Code Generator module enabled
     *
     * @return bool
     */
    public function isCCGModuleEnabled()
    {
        return $this->moduleList->has(self::CCG_MODULE_NAME);
    }
}
