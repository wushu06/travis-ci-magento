<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Status\Relation\FrontendLabel;

use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Status\Relation\FrontendLabel
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
            $this->metadataPool->getMetadata(StatusInterface::class)->getEntityConnectionName()
        );
        $tableName = $this->resourceConnection->getTableName('aw_rma_request_status_frontend_label');
        $connection->delete($tableName, ['status_id = ?' => $entityId]);

        $frontendLabelsToInsert = [];
        /** @var StoreValueInterface $frontendLabel */
        foreach ($entity->getFrontendLabels() as $frontendLabel) {
            $frontendLabelsToInsert[] = [
                'status_id' => $entityId,
                'store_id' => $frontendLabel->getStoreId(),
                'value' => $frontendLabel->getValue()
            ];
        }
        if ($frontendLabelsToInsert) {
            $connection->insertMultiple($tableName, $frontendLabelsToInsert);
        }

        return $entity;
    }
}
