<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Request\Relation\Item;

use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Request\Relation\Item
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
        /** @var RequestInterface $entity */
        if (empty($entity->getCustomFields())) {
            return $entity;
        }
        $entityId = (int)$entity->getId();
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_rma_request_item');

        /** @var RequestItemInterface $item */
        foreach ($entity->getOrderItems() as $item) {
            $bind = [
                'item_id' => $item->getItemId(),
                'qty' => $item->getQty()
            ];
            $itemId = $item->getId();
            if ($itemId) {
                $connection->update($tableName, $bind, ['id = ?' => $itemId]);
            } else {
                $connection->insert($tableName, array_merge($bind, ['request_id' => $entityId]));
                $itemId = $connection->lastInsertId($tableName);
            }
            $this->saveItemCustomFields($item, $itemId);
        }

        return $entity;
    }

    /**
     * Save custom fields by item
     *
     * @param RequestItemInterface $itemEntity
     * @return $this
     */
    private function saveItemCustomFields($itemEntity, $entityId)
    {
        $tableName = $this->resourceConnection->getTableName('aw_rma_request_item_custom_field_value');
        $this->getConnection()->delete($tableName, ['entity_id = ?' => $entityId]);

        $customFieldsToInsert = [];
        $customFields = $itemEntity->getCustomFields() ? : [];
        /** @var RequestCustomFieldValueInterface $customField */
        foreach ($customFields as $customField) {
            $customFieldsToInsert = array_merge(
                $customFieldsToInsert,
                $this->prepareCustomFieldData($entityId, $customField)
            );
        }
        if ($customFieldsToInsert) {
            $this->getConnection()->insertMultiple($tableName, $customFieldsToInsert);
        }

        return $this;
    }

    /**
     * Prepare custom field data to insert
     *
     * @param int $entityId
     * @param RequestCustomFieldValueInterface $customField
     * @return array
     */
    private function prepareCustomFieldData($entityId, $customField)
    {
        $customFieldsToInsert = [];
        if (is_array($customField->getValue())) {
            foreach ($customField->getValue() as $value) {
                $customFieldsToInsert[] = [
                    'entity_id' => $entityId,
                    'field_id' => $customField->getFieldId(),
                    'value' => $value
                ];
            }
        } else {
            $customFieldsToInsert[] = [
                'entity_id' => $entityId,
                'field_id' => $customField->getFieldId(),
                'value' => $customField->getValue()
            ];
        }

        return $customFieldsToInsert;
    }

    /**
     * Retrieve connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(RequestInterface::class)->getEntityConnectionName()
        );
    }
}
