<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option\Action\Operation;

/**
 * Class Replace
 *
 * @package Aheadworks\Rma\Model\CustomField\Option\Action\Operation
 */
class Replace implements OperationInterface
{
    /**
     * Action operation
     */
    const OPERATION = 'replace';

    /**
     * @inheritdoc
     */
    public function isValidForRequest($request)
    {
        return true;
    }
}
