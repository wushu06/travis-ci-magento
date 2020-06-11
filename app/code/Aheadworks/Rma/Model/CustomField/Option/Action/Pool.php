<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option\Action;

use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Rma\Model\CustomField\Option\Action\Operation\Creditmemo;
use Aheadworks\Rma\Model\CustomField\Option\Action\Operation\Replace;
use Aheadworks\Rma\Model\CustomField\Option\Action\Operation\OperationInterface;

/**
 * Class Pool
 *
 * @package Aheadworks\Rma\Model\CustomField\Option\Action
 */
class Pool
{
    /**
     * @var array
     */
    private $operations = [
        Creditmemo::OPERATION => Creditmemo::class,
        Replace::OPERATION => Replace::class,
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $operations
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $operations = []
    ) {
        $this->objectManager = $objectManager;
        $this->operations = array_merge($this->operations, $operations);
    }

    /**
     * Get action by operation
     *
     * @param string $operation
     * @return OperationInterface
     * @throws \Exception
     */
    public function getAction($operation)
    {
        if (isset($this->operations[$operation])) {
            return $this->objectManager->create($this->operations[$operation]);
        } else {
            throw new \Exception(
                sprintf('Operation is not found: %s', $operation)
            );
        }
    }
}
