<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Relation\Attachment;

use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Relation\Attachment
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
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if (!$entity->getAttachments()) {
            return $entity;
        }

        $entityId = (int)$entity->getId();
        $tableName = $this->resourceConnection->getTableName('aw_rma_thread_attachment');
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(ThreadMessageInterface::class)->getEntityConnectionName()
        );
        $connection->delete($tableName, ['message_id = ?' => $entityId]);

        $attachmentsToInsert = [];
        /** @var ThreadMessageAttachmentInterface $attachment */
        foreach ($entity->getAttachments() as $attachment) {
            $attachmentsToInsert[] = [
                'message_id' => $entityId,
                'name' => $attachment->getName(),
                'file_name' => $attachment->getFileName()
            ];
        }
        if ($attachmentsToInsert) {
            $connection->insertMultiple($tableName, $attachmentsToInsert);
        }

        return $entity;
    }
}
