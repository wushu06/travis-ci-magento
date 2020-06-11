<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Relation\Attachment;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterfaceFactory;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Relation\Attachment
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
     * @var ThreadMessageAttachmentInterfaceFactory
     */
    private $threadMessageAttachmentFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param ThreadMessageAttachmentInterfaceFactory $threadMessageAttachmentFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ThreadMessageAttachmentInterfaceFactory $threadMessageAttachmentFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->threadMessageAttachmentFactory = $threadMessageAttachmentFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(CustomFieldInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_rma_thread_attachment'))
                ->where('message_id = :id');
            $attachmentsData = $connection->fetchAll($select, ['id' => $entityId]);

            $attachments = [];
            foreach ($attachmentsData as $attachmentData) {
                $threadMessageAttachmentEntity = $this->threadMessageAttachmentFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $threadMessageAttachmentEntity,
                    $attachmentData,
                    ThreadMessageAttachmentInterface::class
                );
                $attachments[] = $threadMessageAttachmentEntity;
            }
            $entity->setAttachments($attachments);
        }
        return $entity;
    }
}
