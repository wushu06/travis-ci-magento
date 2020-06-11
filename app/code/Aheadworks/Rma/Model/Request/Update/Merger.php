<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Update;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Api\Data\RequestPrintLabelInterface;

/**
 * Class Merger
 *
 * @package Aheadworks\Rma\Model\Request\Update
 */
class Merger
{
    /**
     * Merge two request objects
     *
     * @param RequestInterface $request
     * @param RequestInterface $newRequest
     */
    public function mergeRequest($request, $newRequest)
    {
        if (!empty($newRequest->getStatusId())) {
            $request->setStatusId($newRequest->getStatusId());
        }

        if (!empty($newRequest->getCustomFields())) {
            $request->setCustomFields(
                $this->updateCustomFields($request->getCustomFields(), $newRequest->getCustomFields())
            );
        }

        if (!empty($newRequest->getOrderItems())) {
            $request->setOrderItems(
                $this->updateOrderItems($request->getOrderItems(), $newRequest->getOrderItems())
            );
        }

        if (!empty($newRequest->getThreadMessage())) {
            $request->setThreadMessage($newRequest->getThreadMessage());
        }

        if (!empty($newRequest->getPrintLabel())) {
            $request->setPrintLabel(
                $this->updatePrintLabel($request->getPrintLabel(), $newRequest->getPrintLabel())
            );
        }
    }

    /**
     * Update custom fields
     *
     * @param RequestCustomFieldValueInterface[] $oldCustomFields
     * @param RequestCustomFieldValueInterface[] $newCustomFields
     * @return RequestCustomFieldValueInterface[]
     */
    private function updateCustomFields($oldCustomFields, $newCustomFields)
    {
        $diffCustomFields = $this->findNewCustomFields($newCustomFields, $oldCustomFields);
        $updatedCustomFields = [];
        foreach ($oldCustomFields as $oldCustomField) {
            $existing = $this->findSameCustomField($oldCustomField, $newCustomFields);
            if ($existing) {
                $updatedCustomFields[] = $existing;
            } else {
                $updatedCustomFields[] = $oldCustomField;
            }
        }

        return array_merge($updatedCustomFields, $diffCustomFields);
    }

    /**
     * Retrieve new custom fields (fields that were not in $oldCustomFields array)
     *
     * @param RequestCustomFieldValueInterface[] $newCustomFields
     * @param RequestCustomFieldValueInterface[] $oldCustomFields
     * @return array
     */
    private function findNewCustomFields($newCustomFields, $oldCustomFields)
    {
        $diffCustomFields = array_udiff($newCustomFields, $oldCustomFields, function ($customField1, $customField2) {
            return $customField1->getFieldId() - $customField2->getFieldId();
        });

        return $diffCustomFields;
    }

    /**
     * Find the same old custom field in the new custom fields list
     *
     * @param RequestCustomFieldValueInterface $oldCustomField
     * @param RequestCustomFieldValueInterface[] $newCustomFields
     * @return bool|RequestCustomFieldValueInterface
     */
    private function findSameCustomField($oldCustomField, $newCustomFields)
    {
        foreach ($newCustomFields as $newCustomField) {
            if ($oldCustomField->getFieldId() == $newCustomField->getFieldId()) {
                return $newCustomField;
            }
        }
        return false;
    }

    /**
     * Update order items
     *
     * @param RequestItemInterface[] $oldOrderItems
     * @param RequestItemInterface[] $newOrderItems
     * @return RequestItemInterface[]
     */
    private function updateOrderItems($oldOrderItems, $newOrderItems)
    {
        $updatedOrderItems = [];
        foreach ($oldOrderItems as $oldOrderItem) {
            $existing = $this->findSameOrderItem($oldOrderItem, $newOrderItems);
            if ($existing) {
                $updatedCustomFields = $this->updateCustomFields(
                    $oldOrderItem->getCustomFields(),
                    $existing->getCustomFields()
                );
                $existing
                    ->setId($oldOrderItem->getId())
                    ->setQty($oldOrderItem->getQty())
                    ->setCustomFields($updatedCustomFields);
                $updatedOrderItems[] = $existing;
            } else {
                $updatedOrderItems[] = $oldOrderItem;
            }
        }
        return $updatedOrderItems;
    }

    /**
     * Find the same old item in the new item list
     *
     * @param RequestItemInterface $oldOrderItem
     * @param RequestItemInterface[] $newOrderItems
     * @return bool|RequestItemInterface
     */
    private function findSameOrderItem($oldOrderItem, $newOrderItems)
    {
        foreach ($newOrderItems as $newOrderItem) {
            if ($oldOrderItem->getItemId() == $newOrderItem->getItemId()
                && !empty($newOrderItem->getCustomFields())
            ) {
                return $newOrderItem;
            }
        }
        return false;
    }

    /**
     * Update print label
     *
     * @param RequestPrintLabelInterface $oldRequestPrintLabel
     * @param RequestPrintLabelInterface $newRequestPrintLabel
     * @return RequestPrintLabelInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function updatePrintLabel($oldRequestPrintLabel, $newRequestPrintLabel)
    {
        // @todo change region value by region id (if isset region id)
        return $newRequestPrintLabel;
    }
}
