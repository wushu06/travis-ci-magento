<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request;

use Aheadworks\Rma\Model\ResourceModel\Request;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class IncrementIdGenerator
 *
 * @package Aheadworks\Rma\Model\Request
 */
class IncrementIdGenerator
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Request
     */
    private $requestResource;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Request $requestResource
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Request $requestResource
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->requestResource = $requestResource;
    }

    /**
     * Generate increment id
     *
     * @return string
     */
    public function generate()
    {
        return sprintf("%'09u", $this->getNextIncrementId());
    }

    /**
     * Retrieve next increment id
     *
     * @return int
     * @throws LocalizedException
     */
    private function getNextIncrementId()
    {
        $tableName = $this->requestResource->getMainTable();
        $entityStatus = $this->connection->showTableStatus($tableName);
        if (empty($entityStatus['Auto_increment'])) {
            throw new LocalizedException(__('Cannot get autoincrement value'));
        }
        return $entityStatus['Auto_increment'];
    }
}
