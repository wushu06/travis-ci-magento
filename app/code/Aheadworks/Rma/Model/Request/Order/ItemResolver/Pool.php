<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\ItemResolver;

use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType\Bundle;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType\DefaultType;
use Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType\Configurable;

/**
 * Class Pool
 *
 * @package Aheadworks\Rma\Model\Request\Order\QtyAdjustment
 */
class Pool
{
    /**
     * @var array
     */
    private $processors = [
        'default' => DefaultType::class,
        'bundle' => Bundle::class,
        'configurable' => Configurable::class
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ItemResolverInterface[]
     */
    private $productTypeInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $processors
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $processors = []
    ) {
        $this->objectManager = $objectManager;
        $this->processors = array_merge($this->processors, $processors);
    }

    /**
     * Get item resolver by product type
     *
     * @param string $type
     * @return ItemResolverInterface
     */
    public function getItemResolver($type)
    {
        if (!isset($this->productTypeInstances[$type])) {
            if (isset($this->processors[$type])) {
                $this->productTypeInstances[$type] = $this->objectManager->create($this->processors[$type]);
            } else {
                $this->productTypeInstances[$type] = $this->objectManager->create($this->processors['default']);
            }
        }
        return $this->productTypeInstances[$type];
    }
}
