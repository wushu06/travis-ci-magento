<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option\Action\Operation;

use Aheadworks\Rma\Api\Data\RequestInterface;

/**
 * Class Replace
 * @package Aheadworks\Rma\Model\CustomField\Option\Action\Operation
 */
interface OperationInterface
{
    /**
     * Is valid for request
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function isValidForRequest($request);
}
