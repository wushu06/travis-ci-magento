<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Request\Relation\CustomField;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Request\Relation\CustomField
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
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(RequestInterface::class)->getEntityConnectionName()
        );
        $tableName = $this->resourceConnection->getTableName('aw_rma_request_custom_field_value');
        $connection->delete($tableName, ['entity_id = ?' => $entityId]);

        $customFieldsToInsert = [];
        /** @var RequestCustomFieldValueInterface $customField */
        foreach ($entity->getCustomFields() as $customField) {
            $customFieldsToInsert = array_merge($customFieldsToInsert, $this->prepareData($entityId, $customField));
        }
        if ($customFieldsToInsert) {
            $connection->insertMultiple($tableName, $customFieldsToInsert);
        }

        return $entity;
    }

    /**
     * Prepare data to insert
     *
     * @param int $entityId
     * @param RequestCustomFieldValueInterface $customField
     * @return array
     */
    private function prepareData($entityId, $customField)
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
}
