<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierInterface;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country as CountrySource;
use Magento\Directory\Helper\Data as DirectoryHelper;

/**
 * Class CountryId
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\Attribute
 */
class CountryId implements ModifierInterface
{
    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var CountrySource
     */
    private $countrySource;

    /**
     * @param DirectoryHelper $directoryHelper
     * @param CountrySource $countrySource
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        CountrySource $countrySource
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->countrySource = $countrySource;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($metadata)
    {
        $metadata['options'] = $this->countrySource->getAllOptions();
        $metadata['options'] = $this->orderCountryOptions($metadata['options']);
        return $metadata;
    }

    /**
     * Reorder country options. Move top countries to the beginning of the list
     *
     * @param array $options
     * @return array
     */
    private function orderCountryOptions($options)
    {
        $topCountryCodes = $this->directoryHelper->getTopCountryCodes();
        if (!empty($topCountryCodes)) {
            $headOptions = [];
            $tailOptions = [[
                'value' => 'delimiter',
                'label' => '──────────',
                'disabled' => true,
            ]];

            foreach ($options as $option) {
                if (empty($option['value']) || in_array($option['value'], $topCountryCodes)) {
                    array_push($headOptions, $option);
                } else {
                    array_push($tailOptions, $option);
                }
            }
            return array_merge($headOptions, $tailOptions);
        }
        return $options;
    }
}
