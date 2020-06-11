<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Request\Relation\CustomField;

use Aheadworks\Rma\Model\CustomField\Processor\ReadHandler as CustomFieldReadHandlerProcessor;
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
 * @package Aheadworks\Rma\Model\ResourceModel\Request\Relation\CustomField
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
     * @param RequestCustomFieldValueInterfaceFactory $requestCustomFieldValueFactory
     * @param CustomFieldReadHandlerProcessor $customFieldReadHandlerProcessor
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        RequestCustomFieldValueInterfaceFactory $requestCustomFieldValueFactory,
        CustomFieldReadHandlerProcessor $customFieldReadHandlerProcessor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->requestCustomFieldValueFactory = $requestCustomFieldValueFactory;
        $this->customFieldReadHandlerProcessor = $customFieldReadHandlerProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(RequestInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_rma_request_custom_field_value'))
                ->where('entity_id = :id');
            $customFieldsData = $connection->fetchAll($select, ['id' => $entityId]);
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
            /** @var RequestInterface $entity */
            $entity->setCustomFields($customFields);
        }
        return $entity;
    }
}
