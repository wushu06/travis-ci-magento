<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option\Action;

use Magento\Framework\Config\Data as ConfigData;
use Aheadworks\Rma\Model\CustomField\Option\Action\ConfigDefault\Reader\Xml;
use Magento\Framework\Config\CacheInterface;

/**
 * Class ConfigDefault
 *
 * @package Aheadworks\Rma\Model\CustomField\Option\Action
 */
class ConfigDefault extends ConfigData
{
    /**
     * @param Xml $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        Xml $reader,
        CacheInterface $cache,
        $cacheId = 'aheadworks_rma_custom_field_option_action_config_default_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
