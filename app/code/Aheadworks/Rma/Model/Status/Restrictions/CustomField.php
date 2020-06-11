<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status\Restrictions;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Status\RestrictionsInterface;
use Aheadworks\Rma\Model\Status\Restrictions\CustomField\ActionResolver;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomField
 *
 * @package Aheadworks\Rma\Model\Status\Restrictions
 */
class CustomField
{
    /**
     * @var ActionResolver
     */
    private $actionResolver;

    /**
     * @param ActionResolver $actionResolver
     */
    public function __construct(
        ActionResolver $actionResolver
    ) {
        $this->actionResolver = $actionResolver;
    }

    /**
     * Update actions in restriction
     *
     * @param RestrictionsInterface $restriction
     * @param RequestInterface $request
     * @throws LocalizedException
     */
    public function update($restriction, $request)
    {
        $actions = $restriction->getAvailableForActions();
        $actions = $this->actionResolver->resolveRequestActions($request, true, $actions);

        $orderItems = $request->getOrderItems();
        foreach ($orderItems as $orderItem) {
            $status = $request->getStatusId();
            $actions = $this->actionResolver->resolveRequestItemActions($orderItem, $status, true, $actions);
        }
        $restriction->setAvailableForActions($actions);
    }
}
