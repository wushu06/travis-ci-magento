<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver;

use Magento\Directory\Model\CountryFactory;

/**
 * Class Country
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver
 */
class Country
{
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        CountryFactory $countryFactory
    ) {
        $this->countryFactory = $countryFactory;
    }

    /**
     * Retrieve country name
     *
     * @param $countryId
     * @return string
     */
    public function getCountry($countryId)
    {
        $country = $this->countryFactory->create()->load($countryId);
        return $country->getName();
    }
}
