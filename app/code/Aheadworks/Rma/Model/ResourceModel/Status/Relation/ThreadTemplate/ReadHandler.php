<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Status\Relation\ThreadTemplate;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\Data\StoreValueInterfaceFactory;
use Aheadworks\Rma\Model\StorefrontValueResolver;
use Magento\Framework\Api\DataObjectHelper;
use Aheadworks\Rma\Model\Source\Status\TemplateType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Status\Relation\ThreadTemplate
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entityId = (int)$entity->getId()) {
            /** @var StatusInterface $entity */
            $entity->setThreadTemplates($this->getTemplates($entityId));
            $entity->setStorefrontThreadTemplate(
                $this->storefrontValueResolver->getStorefrontValue(
                    $entity->getThreadTemplates(),
                    $arguments['store_id']
                )
            );
        }
        return $entity;
    }

    /**
     * Retrieve templates by type
     *
     * @param int $entityId
     * @return array
     */
    private function getTemplates($entityId)
    {
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(StatusInterface::class)->getEntityConnectionName()
        );
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('aw_rma_request_status_thread_template'),
                ['store_id', 'value']
            )->where('status_id = :id');
        $templatesData = $connection->fetchAll($select, ['id' => $entityId]);

        $templates = [];
        foreach ($templatesData as $template) {
            $templateEntity = $this->storeValueFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $templateEntity,
                $template,
                StoreValueInterface::class
            );
            $templates[] = $templateEntity;
        }

        return $templates;
    }
}
