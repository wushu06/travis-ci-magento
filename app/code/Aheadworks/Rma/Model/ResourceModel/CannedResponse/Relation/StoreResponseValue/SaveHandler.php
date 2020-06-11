<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CannedResponse\Relation\StoreResponseValue;

use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\CannedResponseInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\Rma\Model\ResourceModel\CannedResponse\Relation\StoreResponseValue
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(CannedResponseInterface::class)->getEntityConnectionName()
        );
        $tableName = $this->resourceConnection->getTableName('aw_rma_canned_response_text');
        $connection->delete($tableName, ['response_id = ?' => $entityId]);

        $storeResponseValuesToInsert = [];
        /** @var StoreValueInterface $frontendLabel */
        foreach ($entity->getStoreResponseValues() as $storeResponseValue) {
            $storeResponseValuesToInsert[] = [
                'response_id' => $entityId,
                'store_id' => $storeResponseValue->getStoreId(),
                'value' => $storeResponseValue->getValue()
            ];
        }
        if ($storeResponseValuesToInsert) {
            $connection->insertMultiple($tableName, $storeResponseValuesToInsert);
        }

        return $entity;
    }
}
