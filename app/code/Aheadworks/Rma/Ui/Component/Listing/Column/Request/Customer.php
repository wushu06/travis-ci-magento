<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Listing\Column\Request;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Rma\Model\Request\Resolver\Customer as CustomerResolver;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory as RmaRequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestInterface as RmaRequestInterface;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Customer
 *
 * @package Aheadworks\Rma\Ui\Component\Listing\Column\Request
 */
class Customer extends Column
{
    /**
     * @var CustomerResolver
     */
    private $customerResolver;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RmaRequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerResolver $customerResolver
     * @param DataObjectHelper $dataObjectHelper
     * @param RmaRequestInterfaceFactory $requestFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerResolver $customerResolver,
        DataObjectHelper $dataObjectHelper,
        RmaRequestInterfaceFactory $requestFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->customerResolver = $customerResolver;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $request = $this->requestFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $request,
                    $item,
                    RmaRequestInterface::class
                );
                $customerInfo = sprintf(
                    '%s, %s',
                    $this->customerResolver->getName($request),
                    $this->customerResolver->getEmail($request)
                );
                $item[$fieldName] = $customerInfo;
            }
        }
        return $dataSource;
    }
}
