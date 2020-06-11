<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Status\Relation\EmailTemplate;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface;
use Aheadworks\Rma\Model\Source\Status\TemplateType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Status\Relation\EmailTemplate
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
        /** @var StatusInterface $entity */
        $this->updateTemplatesByType(TemplateType::CUSTOMER, $entity, $entity->getCustomerTemplates());
        $this->updateTemplatesByType(TemplateType::ADMIN, $entity, $entity->getAdminTemplates());

        return $entity;
    }

    /**
     * Update templates by type
     *
     * @param string $templateType
     * @param StatusInterface $entity
     * @param array $entityTemplates
     * @return $this
     */
    private function updateTemplatesByType($templateType, $entity, $entityTemplates)
    {
        $entityId = (int)$entity->getId();
        $tableName = $this->resourceConnection->getTableName('aw_rma_request_status_email_template');
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(StatusInterface::class)->getEntityConnectionName()
        );
        $connection->delete($tableName, ['status_id = ?' => $entityId, 'template_type = ?' => $templateType]);

        if ($templateType == TemplateType::CUSTOMER && !$entity->isEmailCustomer()) {
            $entity->setCustomerTemplates([]);
            return $this;
        }

        if ($templateType == TemplateType::ADMIN && !$entity->isEmailAdmin()) {
            $entity->setAdminTemplates([]);
            return $this;
        }

        $templateIdsToInsert = [];
        /** @var StatusEmailTemplateInterface $entityTemplate */
        foreach ($entityTemplates as $entityTemplate) {
            $templateIdsToInsert[] = [
                'status_id' => $entityId,
                'template_type' => $templateType,
                'store_id' => $entityTemplate->getStoreId(),
                'value' => $entityTemplate->getValue(),
                'custom_text' => $entityTemplate->getCustomText()
            ];
        }
        if ($templateIdsToInsert) {
            $connection->insertMultiple($tableName, $templateIdsToInsert);
        }

        return $this;
    }
}
