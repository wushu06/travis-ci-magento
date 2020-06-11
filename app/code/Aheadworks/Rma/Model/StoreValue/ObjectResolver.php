<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\StoreValue;

use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class ObjectResolver
 * @package Aheadworks\Rma\Model\StoreValue
 */
class ObjectResolver
{
    /**
     * @var StoreValueInterfaceFactory
     */
    private $storeValueFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param StoreValueInterfaceFactory $storeValueFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        StoreValueInterfaceFactory $storeValueFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->storeValueFactory = $storeValueFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Resolve row storeValue
     *
     * @param StoreValueInterface[]|array $storeValueItem
     * @return StoreValueInterface
     */
    public function resolve($storeValueItem)
    {
        if ($storeValueItem instanceof StoreValueInterface) {
            $storeValueObject = $storeValueItem;
        } else {
            /** @var StoreValueInterface $labelObject */
            $storeValueObject = $this->storeValueFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $storeValueObject,
                $storeValueItem,
                StoreValueInterface::class
            );
        }
        return $storeValueObject;
    }
}
