<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Setup;

use Aheadworks\Rma\Model\ThreadMessage\Attachment\FileUploader;
use Aheadworks\Rma\Model\Source\CustomField\StatusType;
use Aheadworks\Rma\Model\Source\Status\TemplateType;
use Aheadworks\Rma\Model\UnserializeResolver;
use Magento\Framework\File\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Aheadworks\Rma\Model\Status\ConfigDefault as StatusConfigDefault;
use Magento\Framework\Filesystem;
use Aheadworks\Rma\Model\Source\Request\Status;

/**
 * Class UpgradeSchema120
 *
 * @package Aheadworks\Rma\Setup
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeSchema120
{
    /**
     * @var StatusConfigDefault
     */
    private $statusConfigDefault;

    /**
     * @var WriteFactory
     */
    private $fileWriteFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var UnserializeResolver
     */
    private $unserializeResolver;

    /**
     * @param StatusConfigDefault $statusConfigDefault
     * @param WriteFactory $fileWriteFactory
     * @param Filesystem $filesystem
     * @param UnserializeResolver $unserializeResolver
     */
    public function __construct(
        StatusConfigDefault $statusConfigDefault,
        WriteFactory $fileWriteFactory,
        Filesystem $filesystem,
        UnserializeResolver $unserializeResolver
    ) {
        $this->statusConfigDefault = $statusConfigDefault;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->unserializeResolver = $unserializeResolver;
        $this->filesystem = $filesystem;
    }

    /**
     * Upgrade schema
     *
     * @param SchemaSetupInterface $setup
     */
    public function upgrade(SchemaSetupInterface $setup)
    {
        $this->addCustomFieldRelationTables($setup);
        $this->changeCustomFieldOptionValueTable($setup);
        $this->changeCustomFieldTable($setup);
        $this->migrateCustomFieldDataToRelationTable($setup);
        $this->migrateCustomFieldAttributeData($setup);

        $this->addRequestStatusRelationTables($setup);
        $this->changeRequestTableColumns($setup);
        $this->migrateRequestStatusAttributeData($setup);
        $this->addRequestIncrementId($setup);

        $this->changeAttachmentsTableColumns($setup);
        $this->migrateAttachments($setup);
    }

    /**
     * Install schema
     *
     * @param SchemaSetupInterface $setup
     */
    public function install(SchemaSetupInterface $setup)
    {
        $this->addCustomFieldRelationTables($setup);
        $this->addRequestStatusRelationTables($setup);
    }

    /**
     * Add relation table to custom field table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addCustomFieldRelationTables(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'aw_rma_custom_field_website'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aw_rma_custom_field_website')
        )->addColumn(
            'field_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Custom Field ID'
        )->addColumn(
            'website_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Website ID'
        )->addIndex(
            $setup->getIdxName('aw_rma_custom_field_website', ['field_id']),
            ['field_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_custom_field_website', ['website_id']),
            ['website_id']
        )->addForeignKey(
            $setup->getFkName('aw_rma_custom_field_website', 'field_id', 'aw_rma_custom_field', 'id'),
            'field_id',
            $setup->getTable('aw_rma_custom_field'),
            'id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('aw_rma_custom_field_website', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $setup->getTable('store_website'),
            'website_id',
            Table::ACTION_CASCADE
        )->setComment(
            'AW Rma Custom Field To Website Relation Table'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_custom_field_status'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aw_rma_custom_field_status')
        )->addColumn(
            'field_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Custom Field ID'
        )->addColumn(
            'status_type',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Status Type'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Status'
        )->addIndex(
            $setup->getIdxName('aw_rma_custom_field_status', ['field_id']),
            ['field_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_custom_field_status', ['status_type', 'status']),
            ['status_type', 'status']
        )->addForeignKey(
            $setup->getFkName(
                'aw_rma_custom_field_status',
                'field_id',
                'aw_rma_custom_field',
                'id'
            ),
            'field_id',
            $setup->getTable('aw_rma_custom_field'),
            'id',
            Table::ACTION_CASCADE
        )->setComment(
            'AW Rma Custom Field To Status Relation Table'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_custom_field_frontend_label'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aw_rma_custom_field_frontend_label')
        )->addColumn(
            'field_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Custom Field ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Value'
        )->addIndex(
            $setup->getIdxName('aw_rma_custom_field_frontend_label', ['field_id']),
            ['field_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_custom_field_frontend_label', ['store_id']),
            ['store_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_custom_field_frontend_label', ['value']),
            ['value']
        )->addForeignKey(
            $setup->getFkName('aw_rma_custom_field_frontend_label', 'field_id', 'aw_rma_custom_field', 'id'),
            'field_id',
            $setup->getTable('aw_rma_custom_field'),
            'id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('aw_rma_custom_field_frontend_label', 'store_id', 'store', 'store_id'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'AW Rma Custom Field Frontend Labels Table'
        );
        $setup->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Change custom field option value table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function changeCustomFieldOptionValueTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_rma_custom_field_option_value');
        if (!$connection->tableColumnExists($tableName, 'id')) {
            return $this;
        }
        $connection->dropColumn($tableName, 'id');

        return $this;
    }

    /**
     * Change custom field table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function changeCustomFieldTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_rma_custom_field');
        if (!$connection->tableColumnExists($tableName, 'is_system')) {
            return $this;
        }
        $connection->dropColumn($tableName, 'is_system');

        return $this;
    }

    /**
     * Migrate and delete serializable fields in aw_rm_custom_field table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function migrateCustomFieldDataToRelationTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        if (!$connection->tableColumnExists($setup->getTable('aw_rma_custom_field'), 'website_ids')
            || !$connection->tableColumnExists($setup->getTable('aw_rma_custom_field'), 'visible_for_status_ids')
            || !$connection->tableColumnExists($setup->getTable('aw_rma_custom_field'), 'editable_for_status_ids')
            || !$connection->tableColumnExists($setup->getTable('aw_rma_custom_field'), 'editable_admin_for_status_ids')
        ) {
            return $this;
        }

        $select = $connection->select()->from(
            $setup->getTable('aw_rma_custom_field'),
            [
                'id',
                'website_ids',
                'visible_for_status_ids',
                'editable_for_status_ids',
                'editable_admin_for_status_ids'
            ]
        );
        $data = $connection->fetchAssoc($select);

        $toInsertWebsite = [];
        $toInsertStatus = [];
        foreach ($data as $row) {
            $websiteIds = $this->unserializeResolver->unserialize($row['website_ids']);
            $visibleForStatusIds = $this->unserializeResolver->unserialize($row['visible_for_status_ids']);
            $editableForStatusIds = $this->unserializeResolver->unserialize($row['editable_for_status_ids']);
            $editableAdminForStatusIds = $this->unserializeResolver->unserialize($row['editable_admin_for_status_ids']);
            $customFieldId = (int)$row['id'];

            foreach ($websiteIds as $websiteId) {
                $toInsertWebsite[] = [
                    'field_id' => $customFieldId,
                    'website_id' => (int)$websiteId,
                ];
            }
            foreach ($visibleForStatusIds as $status) {
                $toInsertStatus[] = [
                    'field_id' => $customFieldId,
                    'status_type' => StatusType::CUSTOMER_VISIBLE,
                    'status' => (int)$status,
                ];
            }
            foreach ($editableForStatusIds as $status) {
                if (in_array($status, [Status::CLOSED, Status::CANCELED])) {
                    continue;
                }
                $toInsertStatus[] = [
                    'field_id' => $customFieldId,
                    'status_type' => StatusType::CUSTOMER_EDITABLE,
                    'status' => (int)$status,
                ];
            }
            foreach ($editableAdminForStatusIds as $status) {
                $toInsertStatus[] = [
                    'field_id' => $customFieldId,
                    'status_type' => StatusType::ADMIN_EDITABLE,
                    'status' => (int)$status,
                ];
            }
        }
        if (count($toInsertWebsite)) {
            $connection->insertMultiple(
                $setup->getTable('aw_rma_custom_field_website'),
                $toInsertWebsite
            );
        }
        if (count($toInsertStatus)) {
            $connection->insertMultiple(
                $setup->getTable('aw_rma_custom_field_status'),
                $toInsertStatus
            );
        }

        $connection->dropColumn($setup->getTable('aw_rma_custom_field'), 'website_ids');
        $connection->dropColumn($setup->getTable('aw_rma_custom_field'), 'visible_for_status_ids');
        $connection->dropColumn($setup->getTable('aw_rma_custom_field'), 'editable_for_status_ids');
        $connection->dropColumn($setup->getTable('aw_rma_custom_field'), 'editable_admin_for_status_ids');

        return $this;
    }

    /**
     * Migrate and delete data from aw_rma_custom_field_attr_value table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function migrateCustomFieldAttributeData(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        if (!$connection->isTableExists($setup->getTable('aw_rma_custom_field_attr_value'))) {
            return $this;
        }

        $select = $connection->select()
            ->from($setup->getTable('aw_rma_custom_field_attr_value'))
            ->where('attribute_code = "frontend_label"');
        $data = $connection->fetchAssoc($select);

        $toInsert = [];
        foreach ($data as $row) {
            $toInsert[] = [
                'field_id' => $row['custom_field_id'],
                'store_id' => $row['store_id'],
                'value' => $row['value']
            ];
        }
        if (count($toInsert)) {
            $connection->insertMultiple(
                $setup->getTable('aw_rma_custom_field_frontend_label'),
                $toInsert
            );
        }

        $connection->dropTable($setup->getTable('aw_rma_custom_field_attr_value'));
        return $this;
    }

    /**
     * Add relation table to request status table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addRequestStatusRelationTables(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'aw_rma_request_status_email_template'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aw_rma_request_status_email_template')
        )->addColumn(
            'status_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Status ID'
        )->addColumn(
            'template_type',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Template Type'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store ID'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Value'
        )->addColumn(
            'custom_text',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Custom Text'
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_email_template', ['status_id']),
            ['status_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_email_template', ['store_id']),
            ['store_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_email_template', ['template_type', 'value']),
            ['template_type', 'value']
        )->addForeignKey(
            $setup->getFkName(
                'aw_rma_request_status_email_template',
                'status_id',
                'aw_rma_request_status',
                'id'
            ),
            'status_id',
            $setup->getTable('aw_rma_request_status'),
            'id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('aw_rma_request_status_email_template', 'store_id', 'store', 'store_id'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'AW Rma Request Status To Email Template Relation Table'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_request_status_thread_template'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aw_rma_request_status_thread_template')
        )->addColumn(
            'status_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Status ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store ID'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Value'
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_thread_template', ['status_id']),
            ['status_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_thread_template', ['store_id']),
            ['store_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_thread_template', ['value']),
            ['value']
        )->addForeignKey(
            $setup->getFkName(
                'aw_rma_request_status_thread_template',
                'status_id',
                'aw_rma_request_status',
                'id'
            ),
            'status_id',
            $setup->getTable('aw_rma_request_status'),
            'id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('aw_rma_request_status_thread_template', 'store_id', 'store', 'store_id'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'AW Rma Request Status To Thread Template Relation Table'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_request_status_frontend_label'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable('aw_rma_request_status_frontend_label')
        )->addColumn(
            'status_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Custom Field ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Value'
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_frontend_label', ['status_id']),
            ['status_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_frontend_label', ['store_id']),
            ['store_id']
        )->addIndex(
            $setup->getIdxName('aw_rma_request_status_frontend_label', ['value']),
            ['value']
        )->addForeignKey(
            $setup->getFkName('aw_rma_request_status_frontend_label', 'status_id', 'aw_rma_request_status', 'id'),
            'status_id',
            $setup->getTable('aw_rma_request_status'),
            'id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('aw_rma_request_status_frontend_label', 'store_id', 'store', 'store_id'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'AW Rma Request Status Frontend Labels Table'
        );
        $setup->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Change request table columns
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function changeRequestTableColumns(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $requestItemCustomFieldValueTableName = $setup->getTable('aw_rma_request_item_custom_field_value');
        if ($connection->tableColumnExists($requestItemCustomFieldValueTableName, 'id')) {
            $connection->dropColumn($requestItemCustomFieldValueTableName, 'id');
        }

        $requestCustomFieldValueTableName = $setup->getTable('aw_rma_request_custom_field_value');
        if ($connection->tableColumnExists($requestCustomFieldValueTableName, 'id')) {
            $connection->dropColumn($requestCustomFieldValueTableName, 'id');
        }

        $requestTableName = $setup->getTable('aw_rma_request');
        if (!$connection->tableColumnExists($requestTableName, 'increment_id')) {
            $connection->addColumn(
                $requestTableName,
                'increment_id',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 100,
                    'nullable' => false,
                    'after' => 'id',
                    'comment'  => 'Request Increment Id'
                ]
            );
        }

        return $this;
    }

    /**
     * Migrate and delete data from aw_rma_status_attr_value table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function migrateRequestStatusAttributeData(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        if (!$connection->isTableExists($setup->getTable('aw_rma_status_attr_value'))) {
            return $this;
        }

        $select = $connection->select()->from($setup->getTable('aw_rma_status_attr_value'));
        $data = $connection->fetchAssoc($select);
        $attributeAssoc = [
            'template_to_admin' => TemplateType::ADMIN,
            'template_to_customer' => TemplateType::CUSTOMER
        ];

        $frontendLabelsToInsert = [];
        $emailTemplatesToInsert = [];
        $threadTemplatesToInsert = [];
        foreach ($data as $row) {
            $statusId = $row['status_id'];
            if ($row['attribute_code'] == 'frontend_label') {
                $frontendLabelsToInsert[] = [
                    'status_id' => $statusId,
                    'store_id' => $row['store_id'],
                    'value' => $row['value']
                ];
            }

            if (in_array($row['attribute_code'], array_keys($attributeAssoc))) {
                if (empty($row['value'])) {
                    continue;
                }
                $templateType = $attributeAssoc[$row['attribute_code']];
                $template = $row['value'] == 'aw_rma_email_template_to_' . $templateType . '_status_' . $statusId
                    ? 'aw_rma_email_template_to_' . $templateType . '_status_changed'
                    : $row['value'];
                $emailTemplatesToInsert[] = [
                    'status_id' => $statusId,
                    'template_type' => $templateType,
                    'store_id' => $row['store_id'],
                    'value' => $template,
                    'custom_text' => $this->getCustomTextFromStatusDefaultData($statusId, $templateType)
                ];
            }
            if ($row['attribute_code'] == 'template_to_thread') {
                if (empty($row['value'])) {
                    continue;
                }
                $threadTemplatesToInsert[] = [
                    'status_id' => $statusId,
                    'store_id' => $row['store_id'],
                    'value' => $row['value']
                ];
            }
        }
        if (count($frontendLabelsToInsert)) {
            $connection->insertMultiple(
                $setup->getTable('aw_rma_request_status_frontend_label'),
                $frontendLabelsToInsert
            );
        }
        if (count($emailTemplatesToInsert)) {
            $connection->insertMultiple(
                $setup->getTable('aw_rma_request_status_email_template'),
                $emailTemplatesToInsert
            );
        }
        if (count($threadTemplatesToInsert)) {
            $connection->insertMultiple(
                $setup->getTable('aw_rma_request_status_thread_template'),
                $threadTemplatesToInsert
            );
        }

        $connection->dropTable($setup->getTable('aw_rma_status_attr_value'));

        return $this;
    }

    /**
     * Add request increment id
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addRequestIncrementId(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_rma_request');
        $select = $connection->select()->from($tableName)->where('(increment_id IS NULL OR increment_id = "")');

        $requests = $connection->fetchAssoc($select);
        foreach ($requests as $request) {
            $incrementId = sprintf("%'09u", (int)$request['id']);
            $connection->update($tableName, ['increment_id' => $incrementId], ['id = ?' => $request['id']]);
        }

        return $this;
    }

    /**
     * Change attachments table columns
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function changeAttachmentsTableColumns(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_rma_thread_attachment');
        if (!$connection->tableColumnExists($tableName, 'file_name')) {
            $connection->addColumn(
                $tableName,
                'file_name',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => Table::DEFAULT_TEXT_SIZE,
                    'nullable' => false,
                    'comment'  => 'File Name On The Server'
                ]
            );
        }

        return $this;
    }

    /**
     * Migrate attachments
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function migrateAttachments(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_rma_thread_attachment');
        if (!$connection->tableColumnExists($tableName, 'content')) {
            return $this;
        }

        $select = $connection->select()->from($tableName);
        $attachments = $connection->fetchAssoc($select);

        foreach ($attachments as $attachment) {
            $fileName = Uploader::getCorrectFileName($attachment['name']);
            $fileName = Uploader::getNewFileName($this->getFilePath($fileName));
            $filePath = $this->getFilePath($fileName);
            $file = $this->fileWriteFactory->create(
                $filePath,
                Filesystem\DriverPool::FILE,
                'w'
            );
            $file->write($attachment['content']);
            $file->close();
            $connection->update($tableName, ['file_name' => $fileName], ['id = ?' => $attachment['id']]);
        }
        $connection->dropColumn($setup->getTable('aw_rma_thread_attachment'), 'content');

        if ($connection->tableColumnExists($tableName, 'id')) {
            $connection->dropColumn($tableName, 'id');
        }

        return $this;
    }

    /**
     * Retrieve file path
     *
     * @param string $fileName
     * @return string
     */
    private function getFilePath($fileName)
    {
        return $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath(FileUploader::FILE_DIR . '/' . $fileName);
    }

    /**
     * Retrieve email custom text from status default data
     *
     * @param int $statusId
     * @param string $templateType
     * @return string
     */
    private function getCustomTextFromStatusDefaultData($statusId, $templateType)
    {
        $statusDefaultData = $this->statusConfigDefault->get();
        foreach ($statusDefaultData as $status) {
            if ($status['id'] == $statusId) {
                foreach ($status['email_template'] as $template) {
                    if ($template['template_type'] == $templateType) {
                        return $template['custom_text'];
                    }
                }
            }
        }
        return '';
    }
}
