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
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\Status
 */
class ReadHandler implements ExtensionInterface
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
        if ($entityId = (int)$entity->getId()) {
            /** @var CustomFieldInterface $entity */
            $entity->setVisibleForStatusIds($this->getStatusIdsByType(StatusType::CUSTOMER_VISIBLE, $entityId));
            $entity->setEditableForStatusIds($this->getStatusIdsByType(StatusType::CUSTOMER_EDITABLE, $entityId));
            $entity->setEditableAdminForStatusIds($this->getStatusIdsByType(StatusType::ADMIN_EDITABLE, $entityId));
        }
        return $entity;
    }

    /**
     * Retrieve status ids by type
     *
     * @param string $statusType
     * @param int $entityId
     * @return array
     */
    private function getStatusIdsByType($statusType, $entityId)
    {
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(CustomFieldInterface::class)->getEntityConnectionName()
        );
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('aw_rma_custom_field_status'), 'status')
            ->where('status_type = ?', $statusType)
            ->where('field_id = :id');

        return $connection->fetchCol($select, ['id' => $entityId]);
    }
}
