<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Setup;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Model\Serialize\SerializerInterface;
use Aheadworks\Rma\Model\Source\CustomField\EditAt;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Aheadworks\Rma\Model\Serialize\Factory;
use Aheadworks\Rma\Setup\Updater\Data\Updater as DataUpdater;

/**
 * Class UpgradeData
 *
 * @package Aheadworks\Rma\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var DataUpdater
     */
    private $updater;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param Factory $factory
     * @param DataUpdater $updater
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomFieldRepositoryInterface $customFieldRepository,
        Factory $factory,
        DataUpdater $updater
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customFieldRepository = $customFieldRepository;
        $this->serializer = $factory->create();
        $this->updater = $updater;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion() && version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->updateCustomFields();
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '1.3.1', '<')) {
            $this->updatePrintLabel($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '1.4.0', '<')) {
            $this->updater->update140($setup);
        }

        $setup->endSetup();
    }

    /**
     * Update custom fields
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function updateCustomFields()
    {
        $customFields = $this->customFieldRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        foreach ($customFields as $customField) {
            $ids = $customField->getEditableAdminForStatusIds() ? : [];
            $ids = array_merge($ids, [EditAt::NEW_REQUEST_PAGE]);
            $customField->setEditableAdminForStatusIds($ids);
            $this->customFieldRepository->save($customField);
        }
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function updatePrintLabel($setup)
    {
        $connection = $setup->getConnection();
        $table = $setup->getTable('aw_rma_request');
        $select = $connection->select()->from(
            $table,
            [
                RequestInterface::ID,
                RequestInterface::PRINT_LABEL
            ]
        );
        $printLabels = $connection->fetchAssoc($select);
        foreach ($printLabels as $printLabel) {
            $unserializedLabel = $this->unserialize($printLabel[RequestInterface::PRINT_LABEL]);
            if ($unserializedLabel !== false) {
                $printLabel[RequestInterface::PRINT_LABEL] = empty($unserializedLabel)
                    ? ''
                    : $this->serializer->serialize($unserializedLabel);

                $connection->update(
                    $table,
                    [
                        RequestInterface::PRINT_LABEL => $printLabel[RequestInterface::PRINT_LABEL]
                    ],
                    RequestInterface::ID . ' = ' . $printLabel[RequestInterface::ID]
                );
            }
        }
    }

    /**
     * Unserialize string with unserialize method
     *
     * @param $string
     * @return array|bool
     */
    private function unserialize($string)
    {
        $result = '';
        if (!empty($string)) {
            $result = @unserialize($string);
            if ($result !== false || $string === 'b:0;') {
            } else {
                $result = false;
            }
        }
        return $result;
    }
}
