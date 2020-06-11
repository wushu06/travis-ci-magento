<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status;

use Aheadworks\Rma\Model\Status\Request\StatusList;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Status\Restrictions\CustomField as CustomFieldRestrictions;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class RestrictionsPool
 *
 * @package Aheadworks\Rma\Model\Status
 */
class RestrictionsPool
{
    /**
     * @var RestrictionsInterfaceFactory
     */
    private $restrictionsFactory;

    /**
     * @var StatusList
     */
    private $statusList;

    /**
     * @var CustomFieldRestrictions
     */
    private $customFieldRestrictions;

    /**
     * @var array
     */
    private $customerRestrictions = [];

    /**
     * @var array
     */
    private $adminRestrictions = [];

    /**
     * @var RestrictionsInterface[]
     */
    private $customerRestrictionsInstances = [];

    /**
     * @var RestrictionsInterface[]
     */
    private $adminRestrictionsInstances = [];

    /**
     * @param RestrictionsInterfaceFactory $restrictionsFactory
     * @param StatusList $statusList
     * @param CustomFieldRestrictions $customFieldRestrictions
     * @param array $customerRestrictions
     * @param array $adminRestrictions
     */
    public function __construct(
        RestrictionsInterfaceFactory $restrictionsFactory,
        StatusList $statusList,
        CustomFieldRestrictions $customFieldRestrictions,
        $customerRestrictions = [],
        $adminRestrictions = []
    ) {
        $this->restrictionsFactory = $restrictionsFactory;
        $this->statusList = $statusList;
        $this->customFieldRestrictions = $customFieldRestrictions;
        $this->customerRestrictions = $customerRestrictions;
        $this->adminRestrictions = $adminRestrictions;
    }

    /**
     * Retrieves restrictions instance
     *
     * @param int $newStatus
     * @param RequestInterface $request
     * @param bool $isAdmin
     * @return RestrictionsInterface
     * @throws \Exception
     */
    public function getRestrictions($newStatus, $request, $isAdmin)
    {
        $restrictionsInstance = $this->getRestrictionsInstanceByType($isAdmin);
        if (!isset($restrictionsInstance[$newStatus])) {
            $restrictions = $this->getRestrictionsByType($isAdmin);
            if (!isset($restrictions[$newStatus])) {
                $this->statusList
                    ->getSearchCriteriaBuilder()
                    ->addFilter(StatusInterface::ID, $newStatus);
                if (empty($this->statusList->retrieve())) {
                    throw new \Exception(sprintf('Unknown status: %s requested', $newStatus));
                }
                $instance = $this->restrictionsFactory->create(['data' => $restrictions['custom']]);
            } else {
                $instance = $this->restrictionsFactory->create(['data' => $restrictions[$newStatus]]);
            }
            if (!$instance instanceof RestrictionsInterface) {
                throw new \Exception(
                    sprintf('Restrictions instance %s does not implement required interface.', $newStatus)
                );
            }
            $this->updateWithCustomFieldRestrictions($instance, $request, $isAdmin);
            $restrictionsInstance = $this->cachedRestrictionsByType($newStatus, $instance, $isAdmin);
        }
        return $restrictionsInstance[$newStatus];
    }

    /**
     * Retrieve restrictions by type
     *
     * @param bool $isAdmin
     * @return array
     */
    private function getRestrictionsByType($isAdmin)
    {
        return $isAdmin ? $this->adminRestrictions : $this->customerRestrictions;
    }

    /**
     * Add additional restrictions depending on custom fields
     *
     * @param RestrictionsInterface $instance
     * @param RequestInterface $request
     * @param bool $isAdmin
     * @throws LocalizedException
     */
    private function updateWithCustomFieldRestrictions($instance, $request, $isAdmin)
    {
        if ($isAdmin) {
            $this->customFieldRestrictions->update($instance, $request);
        }
    }

    /**
     * Retrieve restrictions instance by type
     *
     * @param bool $isAdmin
     * @return RestrictionsInterface[]
     */
    private function getRestrictionsInstanceByType($isAdmin)
    {
        return $isAdmin ? $this->adminRestrictionsInstances : $this->customerRestrictionsInstances;
    }

    /**
     * Cached restrictions by type
     *
     * @param int $status
     * @param RestrictionsInterface $instance
     * @param bool $isAdmin
     * @return array
     */
    private function cachedRestrictionsByType($status, $instance, $isAdmin)
    {
        if ($isAdmin) {
            $this->adminRestrictionsInstances[$status] = $instance;

            return $this->adminRestrictionsInstances;
        } else {
            $this->customerRestrictionsInstances[$status] = $instance;

            return $this->customerRestrictionsInstances;
        }
    }
}
