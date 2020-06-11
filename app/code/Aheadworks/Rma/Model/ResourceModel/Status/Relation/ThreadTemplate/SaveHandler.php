<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Status\Relation\ThreadTemplate;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Model\Source\Status\TemplateType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Status\Relation\ThreadTemplate
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
        /** @var StatusInterface $entity */
        $this->updateTemplates($entity, $entity->getThreadTemplates());

        return $entity;
    }

    /**
     * Update templates
     *
     * @param StatusInterface $entity
     * @param array $entityTemplates
     * @return $this
     */
    private function updateTemplates($entity, $entityTemplates)
    {
        $entityId = (int)$entity->getId();
        $tableName = $this->resourceConnection->getTableName('aw_rma_request_status_thread_template');
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(StatusInterface::class)->getEntityConnectionName()
        );
        $connection->delete($tableName, ['status_id = ?' => $entityId]);

        if (!$entity->isThread()) {
            $entity->setThreadTemplates([]);
            return $this;
        }

        $templateIdsToInsert = [];
        /** @var StoreValueInterface $entityTemplate */
        foreach ($entityTemplates as $entityTemplate) {
            $templateIdsToInsert[] = [
                'status_id' => $entityId,
                'store_id' => $entityTemplate->getStoreId(),
                'value' => $entityTemplate->getValue()
            ];
        }
        if ($templateIdsToInsert) {
            $connection->insertMultiple($tableName, $templateIdsToInsert);
        }

        return $this;
    }
}
