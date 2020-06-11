<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Relation\OwnerName;

use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\ThreadMessage\Relation\OwnerName
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
        if ((int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(ThreadMessageInterface::class)->getEntityConnectionName()
            );
            $tableName = $entity->getOwnerType() == Owner::ADMIN ? 'admin_user' : 'customer_entity';
            $idFieldName = $entity->getOwnerType() == Owner::ADMIN ? 'user_id' : 'entity_id';

            $select = $connection->select()
                ->from($this->resourceConnection->getTableName($tableName), ['firstname', 'lastname'])
                ->where($idFieldName. ' = ?', $entity->getOwnerId());
            if ($result = $connection->fetchRow($select)) {
                $entity->setOwnerName($result['firstname'] . ' ' . $result['lastname']);
            }
        }
        return $entity;
    }
}
