<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Resolver;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Model\Source\CustomField\Refers;

/**
 * Class Request
 *
 * @package Aheadworks\Rma\Model\CustomField\Resolver
 */
class Request
{
    /**
     * Get custom field Ids assigned to request by refersTo
     *
     * @param RequestInterface $request
     * @param string $refersTo
     * @return array
     */
    public function getCustomFieldIdsByRequest($request, $refersTo)
    {
        $resultIds = [];
        if ($refersTo == Refers::REQUEST) {
            $customFields = $request->getCustomFields() ? : [];
            $resultIds = $this->getIds($resultIds, $customFields);
        } else {
            $items = $request->getOrderItems();
            /** @var RequestItemInterface $item */
            foreach ($items as $item) {
                $customFields = $item->getCustomFields() ? : [];
                $resultIds = $this->getIds($resultIds, $customFields);
            }
        }

        return $resultIds;
    }

    /**
     * Get Ids from custom fields
     *
     * @param array $resultIds
     * @param RequestCustomFieldValueInterface[] $customFields
     * @return array
     */
    private function getIds($resultIds, $customFields)
    {
        foreach ($customFields as $customField) {
            if (!in_array($customField->getFieldId(), $resultIds)) {
                $resultIds[] = $customField->getFieldId();
            }
        }

        return $resultIds;
    }
}
