<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status\Restrictions\CustomField;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Model\CustomFieldRepository;
use Aheadworks\Rma\Api\CustomFieldOptionActionRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;

/**
 * Class ActionResolver
 *
 * @package Aheadworks\Rma\Model\Status\Restrictions\CustomField
 */
class ActionResolver
{
    /**
     * @var CustomFieldOptionActionRepositoryInterface
     */
    private $actionRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @param CustomFieldRepository $customFieldRepository
     * @param CustomFieldOptionActionRepositoryInterface $actionRepository
     */
    public function __construct(
        CustomFieldRepository $customFieldRepository,
        CustomFieldOptionActionRepositoryInterface $actionRepository
    ) {
        $this->customFieldRepository = $customFieldRepository;
        $this->actionRepository = $actionRepository;
    }

    /**
     * Get actions available for whole request if any
     *
     * @param RequestInterface $request
     * @param bool $checkAllOptions should check all options or only selected
     * @param array $actions
     * @return array
     * @throws LocalizedException
     */
    public function resolveRequestActions($request, $checkAllOptions = false, $actions = [])
    {
        return $this->resolve(
            $actions,
            $request->getStatusId(),
            $request->getCustomFields(),
            $checkAllOptions
        );
    }

    /**
     * Get actions available for request item
     *
     * @param RequestItemInterface $requestItem
     * @param int $status
     * @param bool $checkAllOptions should check all options or only selected
     * @param array $actions
     * @return array
     * @throws LocalizedException
     */
    public function resolveRequestItemActions($requestItem, $status, $checkAllOptions = false, $actions = [])
    {
        return $this->resolve(
            $actions,
            $status,
            $requestItem->getCustomFields(),
            $checkAllOptions
        );
    }

    /**
     * Resolve custom field option actions
     *
     * @param array $actions
     * @param int $status
     * @param RequestCustomFieldValueInterface[] $customFieldValues
     * @param bool $checkAllOptions
     * @return array
     * @throws LocalizedException
     */
    private function resolve($actions, $status, $customFieldValues, $checkAllOptions)
    {
        foreach ($customFieldValues as $customFieldValue) {
            $customField = $this->customFieldRepository->get($customFieldValue->getFieldId());
            $options = $customField->getOptions();
            foreach ($options as $option) {
                if (!$checkAllOptions && $customFieldValue->getValue() != $option->getId()) {
                    continue;
                }
                if ($option->getActionId() && in_array($status, $option->getActionStatuses())) {
                    $action = $this->actionRepository->get($option->getActionId());
                    if (!in_array($action->getOperation(), $actions)) {
                        $actions[] = $action->getOperation();
                    }
                }
            }
        }

        return $actions;
    }
}
