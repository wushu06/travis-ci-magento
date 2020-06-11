<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor;

use Magento\Framework\UrlInterface;
use Aheadworks\Rma\Model\Request\Resolver\Order as OrderResolver;

/**
 * Class OrderInfoProcessor
 *
 * @package Aheadworks\Rma\Ui\DataProvider\Request\Form\DataProcessor
 */
class OrderInfoProcessor
{
    /**
     * @var OrderResolver
     */
    private $orderResolver;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param OrderResolver $orderResolver
     * @param UrlInterface $url
     */
    public function __construct(
        OrderResolver $orderResolver,
        UrlInterface $url
    ) {
        $this->orderResolver = $orderResolver;
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
        $preparedData = [];
        if (isset($data['order_id']) && $data['order_id']) {
            $orderId = $data['order_id'];
            switch ($dataScope) {
                case 'order_increment_id':
                    $incrementId = $this->orderResolver->getIncrementId($orderId);
                    $preparedData[$dataScope . '_url'] = $this->getUrl(
                        'sales/order/view',
                        ['order_id' => $orderId]
                    );
                    $preparedData[$dataScope . '_label'] = '#' . $incrementId;
                    $preparedData[$dataScope] = $incrementId;
                    $preparedData['increment_id'] = $incrementId;
                    break;
            }
        }

        return $preparedData;
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
