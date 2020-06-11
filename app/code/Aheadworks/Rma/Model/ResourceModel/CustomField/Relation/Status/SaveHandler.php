<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\Status;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Model\Source\CustomField\StatusType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\Status
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getId();

        /** @var CustomFieldInterface $entity */
        $this->updateStatusesByType(StatusType::CUSTOMER_VISIBLE, $entityId, $entity->getVisibleForStatusIds());
        $this->updateStatusesByType(StatusType::CUSTOMER_EDITABLE, $entityId, $entity->getEditableForStatusIds());
        $this->updateStatusesByType(StatusType::ADMIN_EDITABLE, $entityId, $entity->getEditableAdminForStatusIds());

        return $entity;
    }

    /**
     * Update statuses by type
     *
     * @param string $statusType
     * @param int $entityId
     * @param array $entityStatuses
     * @return array
     */
    private function updateStatusesByType($statusType, $entityId, $entityStatuses)
    {
        $statusTableName = $this->resourceConnection->getTableName('aw_rma_custom_field_status');
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(CustomFieldInterface::class)->getEntityConnectionName()
        );
        $connection->delete($statusTableName, ['field_id = ?' => $entityId, 'status_type = ?' => $statusType]);

        $statusIdsToInsert = [];
        foreach ($entityStatuses as $status) {
            $statusIdsToInsert[] = [
                'field_id' => $entityId,
                'status_type' => $statusType,
                'status' => $status
            ];
        }
        if ($statusIdsToInsert) {
            $connection->insertMultiple($statusTableName, $statusIdsToInsert);
        }
    }
}
