<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Email;

use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\UrlInterface;

/**
 * Class UrlBuilder
 *
 * @package Aheadworks\Rma\Model\Request\Email
 */
class UrlBuilder
{
    /**
     * @var UrlInterface
     */
    private $frontendUrlBuilder;

    /**
     * @param UrlInterface $frontendUrlBuilder
     */
    public function __construct(
        UrlInterface $frontendUrlBuilder
    ) {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * Get action url
     *
     * @param string $routePath
     * @param string $scope
     * @param array $params
     * @return string
     */
    public function getUrl($routePath, $scope, $params)
    {
        $this->frontendUrlBuilder->setScope($scope);
        $href = $this->frontendUrlBuilder->getUrl(
            $routePath,
            $params
        );

        return $href;
    }
}
