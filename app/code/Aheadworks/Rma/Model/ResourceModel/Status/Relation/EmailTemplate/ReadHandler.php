<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Status\Relation\EmailTemplate;

use Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface;
use Aheadworks\Rma\Api\Data\StatusEmailTemplateInterfaceFactory;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Model\StorefrontValueResolver;
use Magento\Framework\Api\DataObjectHelper;
use Aheadworks\Rma\Model\Source\Status\TemplateType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Status\Relation\EmailTemplate
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
     * @var StatusEmailTemplateInterfaceFactory
     */
    private $statusEmailTemplateFactory;

    /**
     * @var StorefrontValueResolver
     */
    private $storefrontValueResolver;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param StatusEmailTemplateInterfaceFactory $statusEmailTemplateFactory
     * @param StorefrontValueResolver $storefrontValueResolver
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        StatusEmailTemplateInterfaceFactory $statusEmailTemplateFactory,
        StorefrontValueResolver $storefrontValueResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->statusEmailTemplateFactory = $statusEmailTemplateFactory;
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
            $entity->setCustomerTemplates($this->getTemplatesByType(TemplateType::CUSTOMER, $entityId));
            $entity->setStorefrontCustomerTemplate(
                $this->storefrontValueResolver->getStorefrontValueEmailTemplate(
                    $entity->getCustomerTemplates(),
                    $arguments['store_id']
                )
            );
            $entity->setAdminTemplates($this->getTemplatesByType(TemplateType::ADMIN, $entityId));
            $entity->setStorefrontAdminTemplate(
                $this->storefrontValueResolver->getStorefrontValueEmailTemplate(
                    $entity->getAdminTemplates(),
                    $arguments['store_id']
                )
            );
        }
        return $entity;
    }

    /**
     * Retrieve templates by type
     *
     * @param string $template
     * @param int $entityId
     * @return array
     */
    private function getTemplatesByType($template, $entityId)
    {
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(StatusInterface::class)->getEntityConnectionName()
        );
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('aw_rma_request_status_email_template'),
                ['store_id', 'value', 'custom_text']
            )->where('template_type = ?', $template)
            ->where('status_id = :id');
        $templatesData = $connection->fetchAll($select, ['id' => $entityId]);

        $templates = [];
        foreach ($templatesData as $template) {
            $templateEntity = $this->statusEmailTemplateFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $templateEntity,
                $template,
                StatusEmailTemplateInterface::class
            );
            $templates[] = $templateEntity;
        }

        return $templates;
    }
}
