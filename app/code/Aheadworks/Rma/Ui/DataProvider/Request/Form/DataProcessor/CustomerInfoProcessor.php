<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Request\Resolver\Customer as CustomerResolver;
use Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\CustomerInfoProcessor\RequestObjectResolver;
use Magento\Framework\UrlInterface;
use Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor\CustomerInfoProcessor\AddressRenderer;
use Aheadworks\Rma\Model\ResourceModel\Customer\OrderTotals as CustomerOrderTotals;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class CustomerInfoProcessor
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor
 */
class CustomerInfoProcessor
{
    /**
     * @var CustomerResolver
     */
    private $customerResolver;

    /**
     * @var RequestObjectResolver
     */
    private $requestObjectResolver;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * @var CustomerOrderTotals
     */
    private $customerOrderTotals;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param CustomerResolver $customerResolver
     * @param RequestObjectResolver $requestObjectResolver
     * @param AddressRenderer $addressRenderer
     * @param CustomerOrderTotals $customerOrderTotals
     * @param PriceCurrencyInterface $priceCurrency
     * @param UrlInterface $url
     */
    public function __construct(
        CustomerResolver $customerResolver,
        RequestObjectResolver $requestObjectResolver,
        AddressRenderer $addressRenderer,
        CustomerOrderTotals $customerOrderTotals,
        PriceCurrencyInterface $priceCurrency,
        UrlInterface $url
    ) {
        $this->customerResolver = $customerResolver;
        $this->requestObjectResolver = $requestObjectResolver;
        $this->addressRenderer = $addressRenderer;
        $this->customerOrderTotals = $customerOrderTotals;
        $this->priceCurrency = $priceCurrency;
        $this->url = $url;
    }

    /**
     * Process order items data
     *
     * @param array $data
     * @param string $dataScope
     * @return array
     */
    public function process($data, $dataScope)
    {
        if (isset($data['order_id']) && $data['order_id']) {
            if ($request = $this->requestObjectResolver->resolve($data)) {
                return $this->prepareRequestData($request, $dataScope);
            }
        }

        return [];
    }

    /**
     * Prepare request data
     *
     * @param RequestInterface $request
     * @param string $dataScope
     * @return array
     */
    private function prepareRequestData($request, $dataScope)
    {
        $preparedData = [];
        switch ($dataScope) {
            case 'customer_name':
                if ($customerId = $this->customerResolver->getCustomerId($request)) {
                    $preparedData['customer_id'] = $customerId;
                    $preparedData[$dataScope . '_url'] = $this->getUrl(
                        'customer/index/edit',
                        ['id' => $customerId]
                    );
                }
                $preparedData[$dataScope . '_label'] = $this->customerResolver->getName($request);
                $preparedData[$dataScope] = $request->getCustomerName();
                break;
            case 'customer_email':
                $preparedData[$dataScope] = $this->customerResolver->getEmail($request);
                break;
            case 'customer_address':
                $preparedData[$dataScope] = $this->getAddress($request);
                break;
            case 'customer_group':
                $preparedData[$dataScope] = $this->customerResolver->getGroup($request);
                break;
            case 'customer_since':
                $preparedData[$dataScope] =
                    $this->customerResolver->getCreatedAt($request, null, \IntlDateFormatter::LONG);
                break;
            case 'customer_previous_orders':
                $preparedData[$dataScope] = $this->getPreviousOrderDetails($request);
                break;
        }
        return $preparedData;
    }

    /**
     * Retrieve previous order details
     *
     * @param RequestInterface $request
     * @return string
     */
    private function getPreviousOrderDetails($request)
    {
        $customerId = $this->customerResolver->getCustomerId($request);
        if (empty($customerId)) {
            $customerEmail = $this->customerResolver->getEmail($request);
            $totalPurchasedAmount = $this->customerOrderTotals->getTotalPurchasedAmountByEmail(
                $customerEmail,
                $request->getStoreId()
            );
            $totalOrders = $this->customerOrderTotals->getTotalOrdersByEmail(
                $customerEmail,
                $request->getStoreId()
            );
        } else {
            $totalPurchasedAmount = $this->customerOrderTotals->getTotalPurchasedAmountById(
                $customerId,
                $request->getStoreId()
            );
            $totalOrders = $this->customerOrderTotals->getTotalOrdersById(
                $customerId,
                $request->getStoreId()
            );
        }

        $total = $this->priceCurrency->convertAndFormat(
            $totalPurchasedAmount,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $request->getStoreId()
        );

        return sprintf('%s (%s)', $totalOrders, $total);
    }

    /**
     * Retrieve customer address
     *
     * @param RequestInterface $request
     * @return string
     */
    private function getAddress($request)
    {
        $address = $this->customerResolver->getAddress($request);
        if (!empty($address)) {
            return $this->addressRenderer->render($address);
        }

        return '';
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl($route = '', $params = [])
    {
        return $this->url->getUrl($route, $params);
    }
}
