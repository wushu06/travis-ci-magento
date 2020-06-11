<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Update;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Framework\Validator\AbstractValidator;
use Aheadworks\Rma\Model\CustomField\AvailabilityChecker as CustomFieldAvailabilityChecker;
use Aheadworks\Rma\Model\Request\Resolver\Status as StatusResolver;

/**
 * Class Validator
 *
 * @package Aheadworks\Rma\Model\Request\Update
 */
class Validator extends AbstractValidator
{
    /**
     * @var CustomFieldAvailabilityChecker
     */
    private $customFieldAvailabilityChecker;

    /**
     * @var StatusResolver
     */
    private $statusResolver;

    /**
     * @var bool
     */
    private $causedByAdmin;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CustomFieldAvailabilityChecker $customFieldAvailabilityChecker
     * @param StatusResolver $statusResolver
     */
    public function __construct(
        CustomFieldAvailabilityChecker $customFieldAvailabilityChecker,
        StatusResolver $statusResolver
    ) {
        $this->customFieldAvailabilityChecker = $customFieldAvailabilityChecker;
        $this->statusResolver = $statusResolver;
    }

    /**
     * Set caused by admin
     *
     * @param bool $causedByAdmin
     * @return $this
     */
    public function setIsCausedByAdmin($causedByAdmin)
    {
        $this->causedByAdmin = $causedByAdmin;

        return $this;
    }

    /**
     * Get caused by admin
     *
     * @return bool
     */
    public function isCausedByAdmin()
    {
        return $this->causedByAdmin;
    }

    /**
     * Set current request
     *
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get current request status
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Сheсk if you can updated the request data
     *
     * @param RequestInterface $newRequest
     * @return bool
     */
    public function isValid($newRequest)
    {
        $this->_clearMessages();
        if (!empty($newRequest->getStatusId()) && $newRequest->getStatusId() != $this->getRequest()->getStatusId()
            && !$this->canUpdateStatus($newRequest->getStatusId())
        ) {
            $this->_addMessages([__('Request Status cannot be changed.')]);
        }

        if (!empty($newRequest->getCustomFields())
            && !$this->canUpdateCustomFields($newRequest->getCustomFields(), $this->getRequest()->getCustomFields())
        ) {
            $this->_addMessages([__('Request Custom Fields cannot be changed.')]);
        }

        if (!empty($newRequest->getOrderItems())) {
            $this->checkOrderItems($newRequest);
        }

        return empty($this->getMessages());
    }

    /**
     * Check order items
     *
     * @param RequestInterface $newRequest
     * @return $this
     */
    private function checkOrderItems($newRequest)
    {
        foreach ($newRequest->getOrderItems() as $orderItem) {
            $oldCustomFields = null;
            foreach ($this->getRequest()->getOrderItems() as $oldOrderItem) {
                if ($orderItem->getItemId() == $oldOrderItem->getItemId()) {
                    $oldCustomFields = $oldOrderItem->getCustomFields();
                }
            }

            if (!empty($orderItem->getCustomFields())
                && !$this->canUpdateCustomFields($orderItem->getCustomFields(), $oldCustomFields)
            ) {
                $this->_addMessages([__('Items Custom Fields cannot be changed.')]);
            }
        }
        return $this;
    }

    /**
     * Сheck if you can update status
     *
     * @param int $newStatus
     * @return bool
     * @throws \Exception
     */
    private function canUpdateStatus($newStatus)
    {
        return $this->isCausedByAdmin()
            ? true
            : $this->statusResolver->isAvailableForStatus(
                $newStatus,
                $this->getRequest(),
                $this->isCausedByAdmin()
            );
    }

    /**
     * Сheck if you can update custom fields
     *
     * @param RequestCustomFieldValueInterface[] $newCustomFields
     * @param RequestCustomFieldValueInterface[]|null $oldCustomFields
     * @return bool
     */
    private function canUpdateCustomFields($newCustomFields, $oldCustomFields)
    {
        foreach ($newCustomFields as $newCustomField) {
            // todo Add a check that the custom field value is enable
            if (!$this->canEditCustomField($newCustomField, $oldCustomFields)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check can edit custom field
     *
     * @param RequestCustomFieldValueInterface $customField
     * @param RequestCustomFieldValueInterface[]|null $oldCustomFields
     * @return bool
     */
    private function canEditCustomField($customField, $oldCustomFields)
    {
        if (!empty($oldCustomFields)) {
            foreach ($oldCustomFields as $oldCustomField) {
                if ($customField->getFieldId() == $oldCustomField->getFieldId()
                    && $customField->getValue() == $oldCustomField->getValue()
                ) {
                    return true;
                }
            }
        }

        return $this->isCausedByAdmin()
            ? $this->customFieldAvailabilityChecker
                ->canEditableAdminByStatus($customField->getFieldId(), $this->getRequest()->getStatusId())
            : $this->customFieldAvailabilityChecker
                ->canEditableByStatus($customField->getFieldId(), $this->getRequest()->getStatusId());
    }
}
