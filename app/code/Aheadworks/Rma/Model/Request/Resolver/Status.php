<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Resolver;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Status\RestrictionsPool;

/**
 * Class Status
 *
 * @package Aheadworks\Rma\Model\Request\Resolver
 */
class Status
{
    /**
     * @var RestrictionsPool
     */
    private $restrictionsPool;

    /**
     * @param RestrictionsPool $restrictionsPool
     */
    public function __construct(RestrictionsPool $restrictionsPool)
    {
        $this->restrictionsPool = $restrictionsPool;
    }

    /**
     * Check if available for status
     *
     * @param int $status
     * @param RequestInterface $request
     * @param bool $causedByAdmin
     * @return bool
     * @throws \Exception
     */
    public function isAvailableForStatus($status, $request, $causedByAdmin)
    {
        return in_array(
            $request->getStatusId(),
            $this->restrictionsPool
                ->getRestrictions($status, $request, $causedByAdmin)
                ->getAvailableForStatuses()
        );
    }

    /**
     * Check if available action for status
     *
     * @param string $action
     * @param RequestInterface $request
     * @param bool $causedByAdmin
     * @return bool
     * @throws \Exception
     */
    public function isAvailableActionForStatus($action, $request, $causedByAdmin)
    {
        return in_array(
            $action,
            $this->restrictionsPool
                ->getRestrictions($request->getStatusId(), $request, $causedByAdmin)
                ->getAvailableForActions()
        );
    }
}
