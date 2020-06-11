<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status;

use Magento\Framework\Config\Data;
use Aheadworks\Rma\Model\Status\ConfigDefault\Reader\Xml;
use Magento\Framework\Config\CacheInterface;

/**
 * Class ConfigDefault
 *
 * @package Aheadworks\Rma\Model\Status
 */
class ConfigDefault extends Data
{
    /**
     * @param Xml $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        Xml $reader,
        CacheInterface $cache,
        $cacheId = 'aheadworks_rma_status_config_default_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }
}
