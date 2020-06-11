<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;

/**
 * Class AvailabilityChecker
 *
 * @package Aheadworks\Rma\Model\CustomField
 */
class AvailabilityChecker
{
    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @param CustomFieldRepositoryInterface $customFieldRepository
     */
    public function __construct(
        CustomFieldRepositoryInterface $customFieldRepository
    ) {
        $this->customFieldRepository = $customFieldRepository;
    }

    /**
     * Check if can visible by status
     *
     * @param int $customFieldId
     * @param int $status
     * @return bool
     */
    public function canVisibleByStatus($customFieldId, $status)
    {
        $visibleForStatusIds = $this->customFieldRepository->get($customFieldId)->getVisibleForStatusIds();
        if (is_array($visibleForStatusIds)) {
            return in_array($status, $visibleForStatusIds);
        }

        return false;
    }

    /**
     * Check if can editable by status
     *
     * @param int $customFieldId
     * @param int $status
     * @return bool
     */
    public function canEditableByStatus($customFieldId, $status)
    {
        $editableForStatusIds = $this->customFieldRepository->get($customFieldId)->getEditableForStatusIds();
        if (is_array($editableForStatusIds)) {
            return in_array($status, $editableForStatusIds);
        }

        return false;
    }

    /**
     * Check if can editable admin by status
     *
     * @param int $customFieldId
     * @param int $status
     * @return bool
     */
    public function canEditableAdminByStatus($customFieldId, $status)
    {
        $editableForStatusIds = $this->customFieldRepository->get($customFieldId)->getEditableAdminForStatusIds();
        if (is_array($editableForStatusIds)) {
            return in_array($status, $editableForStatusIds);
        }

        return false;
    }
}
