<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Request;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\Listing as MagentoUiListing;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Listing
 *
 * @package Aheadworks\Rma\Ui\Component\Request
 */
class Listing extends MagentoUiListing
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
        parent::__construct($context, $components, $data);
        $this->request = $request;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        if ($this->request->getFullActionName() == 'sales_order_view') {
            $this->setData('buttons', []);
        }
        parent::prepare();
    }
}
