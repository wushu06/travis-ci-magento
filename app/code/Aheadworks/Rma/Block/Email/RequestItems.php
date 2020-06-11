<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Email;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;
use Aheadworks\Rma\Model\CustomField\Resolver\CustomField as CustomFieldResolver;

/**
 * Class RequestItems
 *
 * @method DataObject getRmaRequest()
 * @method int getStoreId()
 * @package Aheadworks\Rma\Block\Email
 */
class RequestItems extends Template
{
    /**
     * @var OrderItemResolver
     */
    private $orderItemResolver;

    /**
     * @var CustomFieldResolver
     */
    private $customFieldResolver;

    /**
     * @param Context $context
     * @param OrderItemResolver $orderItemResolver
     * @param CustomFieldResolver $customFieldResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderItemResolver $orderItemResolver,
        CustomFieldResolver $customFieldResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderItemResolver = $orderItemResolver;
        $this->customFieldResolver = $customFieldResolver;
    }

    /**
     * Retrieve request items
     *
     * @return array
     */
    public function getRequestItems()
    {
        $preparedRequestItems= [];
        $request = $this->getRmaRequest();
        if (!$request) {
            return $preparedRequestItems;
        }
        if (empty($request->getData('items')) || !is_array($request->getData('items'))) {
            return $preparedRequestItems;
        }

        $requestItems = $request->getData('items');
        /** @var RequestItemInterface $requestItem */
        foreach ($requestItems as $requestItem) {
            $preparedRequestItems[] = [
                'name' => $this->orderItemResolver->getName($requestItem->getItemId()),
                'sku' => $this->orderItemResolver->getSku($requestItem->getItemId()),
                'qty' => $requestItem->getQty(),
                'custom_fields' => $this->prepareCustomFields($requestItem->getCustomFields())
            ];
        }

        return $preparedRequestItems;
    }

    /**
     * Prepare custom fields
     *
     * @param RequestCustomFieldValueInterface[] $customFields
     * @return array
     */
    private function prepareCustomFields($customFields)
    {
        $resolvedCustomFields = [];
        foreach ($customFields as $customField) {
            $value = $this->customFieldResolver
                ->getValue($customField->getFieldId(), $customField->getValue(), $this->getStoreId());
            $label = $this->customFieldResolver->getLabel($customField->getFieldId(), $this->getStoreId());
            $resolvedCustomFields[$label] = $value;
        }

        return $resolvedCustomFields;
    }
}
