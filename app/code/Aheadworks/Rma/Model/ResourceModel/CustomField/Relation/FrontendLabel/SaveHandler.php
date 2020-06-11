<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\FrontendLabel;

use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\FrontendLabel
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
            $this->metadataPool->getMetadata(CustomFieldInterface::class)->getEntityConnectionName()
        );
        $tableName = $this->resourceConnection->getTableName('aw_rma_custom_field_frontend_label');
        $connection->delete($tableName, ['field_id = ?' => $entityId]);

        $frontendLabelsToInsert = [];
        /** @var StoreValueInterface $frontendLabel */
        foreach ($entity->getFrontendLabels() as $frontendLabel) {
            $frontendLabelsToInsert[] = [
                'field_id' => $entityId,
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
