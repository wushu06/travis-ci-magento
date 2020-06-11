<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField;

use Magento\Framework\Config\Data as ConfigData;
use Aheadworks\Rma\Model\CustomField\ConfigDefault\Reader\Xml;
use Magento\Framework\Config\CacheInterface;

/**
 * Class ConfigDefault
 *
 * @package Aheadworks\Rma\Model\CustomField
 */
class ConfigDefault extends ConfigData
{
    public function __construct(
        Xml $reader,
        CacheInterface $cache,
        $cacheId = 'aheadworks_rma_custom_field_config_default_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
