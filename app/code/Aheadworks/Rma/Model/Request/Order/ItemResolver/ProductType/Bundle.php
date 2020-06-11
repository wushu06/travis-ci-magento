<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType;

use Aheadworks\Rma\Model\Request\Order\ItemResolver\ItemResolverInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

/**
 * Class Bundle
 *
 * @package Aheadworks\Rma\Model\Request\Order\ItemResolver\ProductType
 */
class Bundle extends DefaultType implements ItemResolverInterface
{
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @param JsonSerializer $jsonSerializer
     */
    public function __construct(JsonSerializer $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @inheritdoc
     */
    public function resolveBuyRequest($buyRequest, $orderItem, $requestItem)
    {
        $productOptions = $orderItem->getProductOptions();
        if (isset($productOptions['bundle_selection_attributes'])) {
            $bundleSelectionAttributes = $this->jsonSerializer->unserialize(
                $productOptions['bundle_selection_attributes']
            );
            if ($bundleSelectionAttributes && isset($buyRequest['bundle_option_qty'])) {
                $optionId = $bundleSelectionAttributes['option_id'];
                $buyRequest['bundle_option_qty'][$optionId] = $requestItem->getQty();
            }
        }

        return $buyRequest;
    }
}
