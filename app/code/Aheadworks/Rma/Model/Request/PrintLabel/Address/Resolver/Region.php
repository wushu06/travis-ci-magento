<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver;

use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region as RegionResource;

/**
 * Class Region
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver
 */
class Region
{
    /**
     * @var array
     */
    private $regionInstancesById = [];

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var RegionResource
     */
    private $regionResource;

    /**
     * @param RegionFactory $regionFactory
     * @param RegionResource $regionResource
     */
    public function __construct(
        RegionFactory $regionFactory,
        RegionResource $regionResource
    ) {
        $this->regionFactory = $regionFactory;
        $this->regionResource = $regionResource;
    }

    /**
     * Get region name
     *
     * @param string $regionId
     * @param string $region
     * @param int $countryId
     * @return string
     */
    public function getRegion($regionId, $region, $countryId)
    {
        $regionName = $region;
        if (!$regionId && is_numeric($region)) {
            $regionInstance = $this->getRegionInstanceById($region);
            if ($regionInstance->getCountryId() == $countryId) {
                $regionName = $regionInstance->getName();
            }
        } elseif ($regionId) {
            $regionInstance = $this->getRegionInstanceById($regionId);
            if ($regionInstance->getCountryId() == $countryId) {
                $regionName = $regionInstance->getName();
            }
        }
        return $regionName;
    }

    /**
     * Get region instance by ID
     *
     * @param int $regionId
     * @return \Magento\Directory\Model\Region
     */
    private function getRegionInstanceById($regionId)
    {
        if (!isset($this->regionInstancesById[$regionId])) {
            $region = $this->regionFactory->create();
            $this->regionResource->load($region, $regionId);
            $this->regionInstancesById[$regionId] = $region;
        }
        return $this->regionInstancesById[$regionId];
    }
}
