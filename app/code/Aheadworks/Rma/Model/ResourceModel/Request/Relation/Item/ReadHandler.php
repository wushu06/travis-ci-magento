<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Request\Relation\Item;

use Aheadworks\Rma\Model\CustomField\Processor\ReadHandler as CustomFieldReadHandlerProcessor;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Request\Relation\ItemCustomField
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
     * @var RequestItemInterfaceFactory
     */
    private $requestItemFactory;

    /**
     * @var RequestCustomFieldValueInterfaceFactory
     */
    private $requestCustomFieldValueFactory;

    /**
     * @var CustomFieldReadHandlerProcessor
     */
    private $customFieldReadHandlerProcessor;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param RequestItemInterfaceFactory $requestItemFactory
     * @param RequestCustomFieldValueInterfaceFactory $requestCustomFieldValueFactory
     * @param CustomFieldReadHandlerProcessor $customFieldReadHandlerProcessor
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        RequestItemInterfaceFactory $requestItemFactory,
        RequestCustomFieldValueInterfaceFactory $requestCustomFieldValueFactory,
        CustomFieldReadHandlerProcessor $customFieldReadHandlerProcessor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->requestItemFactory = $requestItemFactory;
        $this->requestCustomFieldValueFactory = $requestCustomFieldValueFactory;
        $this->customFieldReadHandlerProcessor = $customFieldReadHandlerProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $select = $this->getConnection()->select()
                ->from($this->resourceConnection->getTableName('aw_rma_request_item'))
                ->where('request_id = :id');
            $itemsData = $this->getConnection()->fetchAll($select, ['id' => $entityId]);

            $items = [];
            foreach ($itemsData as $item) {
                $itemEntity = $this->requestItemFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $itemEntity,
                    $item,
                    RequestItemInterface::class
                );
                $items[] = $this->getItemCustomFields($itemEntity);
            }
            /** @var RequestInterface $entity */
            $entity->setOrderItems($items);
        }
        return $entity;
    }

    /**
     * Retrieve custom fields by item
     *
     * @param RequestItemInterface $itemEntity
     * @return RequestItemInterface
     */
    private function getItemCustomFields($itemEntity)
    {
        if ($entityId = (int)$itemEntity->getId()) {
            $select = $this->getConnection()->select()
                ->from($this->resourceConnection->getTableName('aw_rma_request_item_custom_field_value'))
                ->where('entity_id = :id');
            $customFieldsData = $this->getConnection()->fetchAll($select, ['id' => $entityId]);
            $preparedCustomFields = $this->customFieldReadHandlerProcessor->preparedCustomFieldsData($customFieldsData);

            $customFields = [];
            foreach ($preparedCustomFields as $customField) {
                $customFieldEntity = $this->requestCustomFieldValueFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $customFieldEntity,
                    $customField,
                    RequestCustomFieldValueInterface::class
                );
                $customFields[] = $customFieldEntity;
            }
            $itemEntity->setCustomFields($customFields);
        }
        return $itemEntity;
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
