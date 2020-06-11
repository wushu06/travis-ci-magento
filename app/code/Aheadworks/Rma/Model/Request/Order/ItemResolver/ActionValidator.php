<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Order\ItemResolver;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Status\Restrictions\CustomField\ActionResolver;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Api\Data\RequestItemInterface;

/**
 * Class ActionValidator
 *
 * @package Aheadworks\Rma\Model\Request\Order\ItemResolver
 */
class ActionValidator
{
    /**
     * @var bool|null
     */
    private $isValidForWholeRequest = null;

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
     * Check if action is allowed for all items from request
     *
     * @param RequestInterface $request
     * @param string $action
     * @return bool
     *
     * @throws LocalizedException
     */
    public function isValidForRequest($request, $action)
    {
        if ($this->isValidForWholeRequest === null) {
            $requestActions = $this->actionResolver->resolveRequestActions($request);
            $this->isValidForWholeRequest = false;
            if (in_array($action, $requestActions)) {
                $this->isValidForWholeRequest = true;
            }
        }

        return $this->isValidForWholeRequest;
    }

    /**
     * Check if action is allowed for specified item from request
     *
     * @param RequestItemInterface $requestItem
     * @param RequestInterface $request
     * @param string $action
     * @return bool
     * @throws LocalizedException
     */
    public function isValidForRequestItem($requestItem, $request, $action)
    {
        $result = true;
        $itemActions = $this->actionResolver->resolveRequestItemActions($requestItem, $request->getStatusId());

        if ((!empty($itemActions) && !in_array($action, $itemActions))
            || (empty($itemActions) && !$this->isValidForRequest($request, $action))
        ) {
            $result = false;
        }

        return $result;
    }
}
