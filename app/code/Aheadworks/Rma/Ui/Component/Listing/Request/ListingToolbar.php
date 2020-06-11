<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Listing\Request;

use Magento\Ui\Component\Container;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class ListingToolbar
 *
 * @package Aheadworks\Rma\Ui\Component\Listing\Request
 */
class ListingToolbar extends Container
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ContextInterface $context
     * @param RequestInterface $request
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        $components = $this->excludeNotAvailableComponents($components);
        parent::__construct($context, $components, $data);
    }

    /**
     * Exclude not available components
     *
     * @param array $components
     * @return array
     */
    private function excludeNotAvailableComponents($components)
    {
        $excludeComponents = ['listing_massaction' => 0, 'bookmarks' => 0];
        if ($this->request->getFullActionName() == 'sales_order_view') {
            $components = array_diff_key($components, $excludeComponents);
        }

        return $components;
    }
}
