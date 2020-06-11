<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option;

use Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface;
use Magento\Framework\Model\AbstractModel;
use Aheadworks\Rma\Model\ResourceModel\CustomField\Option\Action as ResourceAction;

/**
 * Class Action
 *
 * @package Aheadworks\Rma\Model\CustomField\Option
 */
class Action extends AbstractModel implements CustomFieldOptionActionInterface
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init(ResourceAction::class);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getOperation()
    {
        return $this->getData(self::OPERATION);
    }

    /**
     * @inheritdoc
     */
    public function setOperation($operation)
    {
        return $this->setData(self::OPERATION, $operation);
    }
}
