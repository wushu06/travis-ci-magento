<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\Option;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionInterfaceFactory;
use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterfaceFactory;
use Aheadworks\Rma\Model\Source\CustomField\Type;
use Aheadworks\Rma\Model\StorefrontValueResolver;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField\Relation\Option
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
     * @var CustomFieldOptionInterfaceFactory
     */
    private $customFieldOptionFactory;

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
     * @param CustomFieldOptionInterfaceFactory $customFieldOptionFactory
     * @param StoreValueInterfaceFactory $storeValueFactory
     * @param StorefrontValueResolver $storefrontValueResolver
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        CustomFieldOptionInterfaceFactory $customFieldOptionFactory,
        StoreValueInterfaceFactory $storeValueFactory,
        StorefrontValueResolver $storefrontValueResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customFieldOptionFactory = $customFieldOptionFactory;
        $this->storeValueFactory = $storeValueFactory;
        $this->storefrontValueResolver = $storefrontValueResolver;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId() && in_array($entity->getType(), [Type::MULTI_SELECT, Type::SELECT])) {
            $entityId = (int)$entity->getId();
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(CustomFieldInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_rma_custom_field_option'))
                ->where('field_id = :id')
                ->order('sort_order ' . SortOrder::SORT_ASC);
            $optionsData = $connection->fetchAll($select, ['id' => $entityId]);

            $options = [];
            foreach ($optionsData as $optionData) {
                $optionEntity = $this->customFieldOptionFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $optionEntity,
                    $optionData,
                    CustomFieldOptionInterface::class
                );
                $this->attachOptionValues($optionEntity, $arguments);
                $this->attachOptionActionStatuses($optionEntity, $arguments);
                $options[] = $optionEntity;
            }
            $entity->setOptions($options);
        }
        return $entity;
    }

    /**
     * Attach values to option
     *
     * @param CustomFieldOptionInterface $optionEntity
     * @param array $arguments
     * @throws \Exception
     */
    private function attachOptionValues($optionEntity, $arguments = [])
    {
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(CustomFieldInterface::class)->getEntityConnectionName()
        );
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('aw_rma_custom_field_option_value'))
            ->where('option_id = :id');
        $optionValuesData = $connection->fetchAll($select, ['id' => $optionEntity->getId()]);

        $optionValues = [];
        foreach ($optionValuesData as $optionValueData) {
            /** @var StoreValueInterface $optionValueEntity */
            $optionValueEntity = $this->storeValueFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $optionValueEntity,
                $optionValueData,
                StoreValueInterface::class
            );
            $optionValues[] = $optionValueEntity;
        }

        $optionEntity
            ->setStoreLabels($optionValues)
            ->setStorefrontLabel(
                $this->storefrontValueResolver->getStorefrontValue($optionValues, $arguments['store_id'])
            );
    }

    /**
     * Attach option action statuses
     *
     * @param CustomFieldOptionInterface $optionEntity
     * @param array $arguments
     * @throws \Exception
     */
    private function attachOptionActionStatuses($optionEntity, $arguments = [])
    {
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(CustomFieldInterface::class)->getEntityConnectionName()
        );
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('aw_rma_custom_field_option_action_status'))
            ->where('option_id = :id');
        $actionStatusesData = $connection->fetchAll($select, ['id' => $optionEntity->getId()]);

        $actionStatuses = [];
        foreach ($actionStatusesData as $actionStatusesRow) {
            $actionStatuses[] = $actionStatusesRow['status_id'];
        }

        $optionEntity->setActionStatuses($actionStatuses);
    }
}
