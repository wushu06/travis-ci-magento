<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Layout\Processor;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMetaProvider;
use Magento\Framework\Stdlib\ArrayManager;
use Aheadworks\Rma\Model\Request\PrintLabel\AddressAttributes\Merger;
use Aheadworks\Rma\Model\Request\Resolver\Status as StatusResolver;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Aheadworks\Rma\Api\Data\RequestPrintLabelInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\UrlInterface;

/**
 * Class AddressAttributes
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Layout\Processor
 */
class AddressAttributes
{
    /**
     * @var AttributeMetaProvider
     */
    private $attributeMataProvider;

    /**
     * @var Merger
     */
    private $attributeMerger;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var StatusResolver
     */
    private $statusResolver;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param AttributeMetaProvider $attributeMataProvider
     * @param Merger $attributeMerger
     * @param ArrayManager $arrayManager
     * @param StatusResolver $statusResolver
     * @param DataObjectProcessor $dataObjectProcessor
     * @param UrlInterface $url
     */
    public function __construct(
        AttributeMetaProvider $attributeMataProvider,
        Merger $attributeMerger,
        ArrayManager $arrayManager,
        StatusResolver $statusResolver,
        DataObjectProcessor $dataObjectProcessor,
        UrlInterface $url
    ) {
        $this->attributeMataProvider = $attributeMataProvider;
        $this->attributeMerger = $attributeMerger;
        $this->arrayManager = $arrayManager;
        $this->statusResolver = $statusResolver;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->url = $url;
    }

    /**
     * Process layout
     *
     * @param array $jsLayout
     * @param RequestInterface $request
     * @return array
     */
    public function process($jsLayout, $request)
    {
        $shippingAddressFieldRowsPath = 'components/awRmaViewAddress/children/address-fields/children';
        $shippingAddressFieldRowsLayout = array_merge(
            $this->arrayManager->get($shippingAddressFieldRowsPath, $jsLayout),
            $this->getShippingAddressComponentDetails()
        );
        if ($shippingAddressFieldRowsLayout) {
            $shippingAddressFieldRowsLayout = $this->attributeMerger->merge(
                $this->attributeMataProvider->getMetadata(),
                'awRmaAddressProvider',
                'shippingAddress.print_label',
                $shippingAddressFieldRowsLayout
            );
            $jsLayout = $this->arrayManager->set(
                $shippingAddressFieldRowsPath,
                $jsLayout,
                $shippingAddressFieldRowsLayout
            );
        }

        $shippingAddressConfigPath = 'components/awRmaViewAddress/config';
        $jsLayout = $this->arrayManager->set(
            $shippingAddressConfigPath,
            $jsLayout,
            array_merge(
                $this->arrayManager->get($shippingAddressConfigPath, $jsLayout),
                ['showEditAddress' => $this->statusResolver->isAvailableActionForStatus('print_label', $request, false)]
            )
        );
        $shippingAddressProviderPath = 'components/awRmaAddressProvider';
        $jsLayout = $this->arrayManager->set(
            $shippingAddressProviderPath,
            $jsLayout,
            array_merge(
                $this->arrayManager->get($shippingAddressProviderPath, $jsLayout),
                [
                    'shippingAddress' => $this->getPrintLabel($request),
                    'clientConfig' => [
                        'urls' => [
                            'save' => $this->getSubmitUrl()
                        ]
                    ]
                ]
            )
        );

        return $jsLayout;
    }

    /**
     * Gets shipping address component details
     *
     * @return array
     */
    private function getShippingAddressComponentDetails()
    {
        return [
            'country_id' => [
                'sortOrder' => 115,
            ],
            'region' => [
                'visible' => false,
            ],
            'region_id' => [
                'component' => 'Aheadworks_Rma/js/ui/form/element/region',
                'config' => [
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'customEntry' => 'shippingAddress.print_label.region',
                ],
                'validation' => [
                    'required-entry' => true,
                ],
                'filterBy' => [
                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                    'field' => 'country_id',
                ],
            ],
            'postcode' => [
                'component' => 'Magento_Ui/js/form/element/post-code',
                'validation' => [
                    'required-entry' => true,
                ],
            ]
        ];
    }

    /**
     * Retrieve submit url
     *
     * @return string
     */
    private function getSubmitUrl()
    {
        return $this->url->getUrl('*/*/saveAddress');
    }

    /**
     * Retrieve print label data
     *
     * @param RequestInterface $request
     * @return array
     */
    private function getPrintLabel($request)
    {
        $printLabel = $this->dataObjectProcessor->buildOutputDataArray(
            $request->getPrintLabel(),
            RequestPrintLabelInterface::class
        );

        if (isset($printLabel[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])) {
            $customAttributes = [];
            foreach ($printLabel[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] as $customAttribute) {
                $customAttributes[$customAttribute[AttributeInterface::ATTRIBUTE_CODE]] =
                    $customAttribute[AttributeInterface::VALUE];
            }
            $printLabel[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] = $customAttributes;
        }
        return [RequestInterface::PRINT_LABEL => $printLabel];
    }
}
