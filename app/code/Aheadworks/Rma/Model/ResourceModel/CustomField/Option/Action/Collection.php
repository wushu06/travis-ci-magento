<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CustomField\Option\Action;

use Aheadworks\Rma\Model\ResourceModel\AbstractCollection;
use Aheadworks\Rma\Model\CustomField\Option\Action;
use Aheadworks\Rma\Model\ResourceModel\CustomField\Option\Action as ResourceAction;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface as ActionInterface;

/**
 * Class Collection
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField\Option\Action
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Action::class, ResourceAction::class);
    }
}
