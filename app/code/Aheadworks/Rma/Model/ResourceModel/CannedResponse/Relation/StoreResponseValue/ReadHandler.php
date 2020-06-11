<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CannedResponse\Relation\StoreResponseValue;

use Aheadworks\Rma\Api\Data\CannedResponseInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterfaceFactory;
use Aheadworks\Rma\Model\CannedResponse\StoreValueResolver;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\Rma\Model\ResourceModel\CannedResponse\Relation\StoreResponseValue
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
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StoreValueInterfaceFactory
     */
    private $storeValueFactory;

    /**
     * @var StoreValueResolver
     */
    private $storeValueResolver;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreValueInterfaceFactory $storeValueFactory
     * @param StoreValueResolver $storeValueResolver
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        StoreValueInterfaceFactory $storeValueFactory,
        StoreValueResolver $storeValueResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeValueFactory = $storeValueFactory;
        $this->storeValueResolver = $storeValueResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        /** @var CannedResponseInterface $entity */
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(CannedResponseInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_rma_canned_response_text'))
                ->where('response_id = :id');
            $storeResponseValuesData = $connection->fetchAll($select, ['id' => $entityId]);

            $storeResponseValues = [];
            foreach ($storeResponseValuesData as $storeResponseValue) {
                /** @var StoreValueInterface $storeResponseValueEntity */
                $storeResponseValueEntity = $this->storeValueFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $storeResponseValueEntity,
                    $storeResponseValue,
                    StoreValueInterface::class
                );
                $storeResponseValues[] = $storeResponseValueEntity;
            }
            $entity
                ->setStoreResponseValues($storeResponseValues)
                ->setResponseText(
                    $this->storeValueResolver->getValueByStoreId($storeResponseValues, $arguments['store_id'])
                );
        }
        return $entity;
    }
}
