<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Status\Relation\FrontendLabel;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterfaceFactory;
use Aheadworks\Rma\Model\StorefrontValueResolver;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Status\Relation\FrontendLabel
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
     * @var StorefrontValueResolver
     */
    private $storefrontValueResolver;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreValueInterfaceFactory $storeValueFactory
     * @param StorefrontValueResolver $storefrontValueResolver
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        StoreValueInterfaceFactory $storeValueFactory,
        StorefrontValueResolver $storefrontValueResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeValueFactory = $storeValueFactory;
        $this->storefrontValueResolver = $storefrontValueResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(StatusInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_rma_request_status_frontend_label'))
                ->where('status_id = :id');
            $frontendLabelsData = $connection->fetchAll($select, ['id' => $entityId]);

            $frontendLabels = [];
            foreach ($frontendLabelsData as $frontendLabel) {
                $frontendLabelEntity = $this->storeValueFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $frontendLabelEntity,
                    $frontendLabel,
                    StoreValueInterface::class
                );
                $frontendLabels[] = $frontendLabelEntity;
            }
            $entity
                ->setFrontendLabels($frontendLabels)
                ->setStorefrontLabel(
                    $this->storefrontValueResolver->getStorefrontValue($frontendLabels, $arguments['store_id'])
                );
        }
        return $entity;
    }
}
