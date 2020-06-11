<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\Website;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\Website
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
        $websiteTableName = $this->resourceConnection->getTableName('aw_rma_custom_field_website');
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(CustomFieldInterface::class)->getEntityConnectionName()
        );
        $connection->delete($websiteTableName, ['field_id = ?' => $entityId]);

        $websiteIdsToInsert = [];
        foreach ($entity->getWebsiteIds() as $websiteId) {
            $websiteIdsToInsert[] = [
                'field_id' => $entityId,
                'website_id' => $websiteId
            ];
        }
        if ($websiteIdsToInsert) {
            $connection->insertMultiple($websiteTableName, $websiteIdsToInsert);
        }

        return $entity;
    }
}
