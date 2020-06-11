<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\ThreadMessage;

use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Model\ResourceModel\AbstractCollection;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Aheadworks\Rma\Model\ThreadMessage;
use Aheadworks\Rma\Model\ResourceModel\ThreadMessage as ResourceThreadMessage;
use Magento\Framework\DataObject;

/**
 * Class Collection
 *
 * @package Aheadworks\Rma\Model\ResourceModel\ThreadMessage
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ThreadMessage::class, ResourceThreadMessage::class);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == ThreadMessageAttachmentInterface::FILE_NAME) {
            $this->addFilter($field, $condition, 'public');
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachRelationTable(
            'aw_rma_thread_attachment',
            'id',
            'message_id',
            ['message_id', 'name', 'file_name'],
            'attachments'
        );
        $this
            ->attachOwnerName('admin_user', 'user_id', Owner::ADMIN)
            ->attachOwnerName('customer_entity', 'entity_id', Owner::CUSTOMER);
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinLinkageTable(
            'aw_rma_thread_attachment',
            'id',
            'message_id',
            'file_name',
            'file_name'
        );
        parent::_renderFiltersBefore();
    }

    /**
     * Attach owner name
     *
     * @param string $tableName
     * @param string $linkageColumnName
     * @param int $ownerType
     * @return $this
     */
    private function attachOwnerName($tableName, $linkageColumnName, $ownerType)
    {
        $ids = $this->getOwnerIds($ownerType);
        if (count($ids)) {
            $dataFromTable = $this->getDataFromTable($tableName, $linkageColumnName, $ids);
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $id = $item->getData(ThreadMessageInterface::OWNER_ID);
                $itemOwnerType = $item->getData(ThreadMessageInterface::OWNER_TYPE);
                foreach ($dataFromTable as $data) {
                    if ($data[$linkageColumnName] == $id && $ownerType == $itemOwnerType) {
                        $ownerName = $data['firstname'] . ' ' . $data['lastname'];
                        $item->setData(ThreadMessageInterface::OWNER_NAME, $ownerName);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve owner ids by type
     *
     * @param int $ownerType
     * @return array
     */
    private function getOwnerIds($ownerType)
    {
        $this->load();
        $col = [];
        foreach ($this->getItems() as $item) {
            if ($item->getData(ThreadMessageInterface::OWNER_TYPE) == $ownerType
                && $item->getData(ThreadMessageInterface::IS_AUTO) == 0
            ) {
                $col[] = $item->getData(ThreadMessageInterface::OWNER_ID);
            }
        }

        return $col;
    }

    /**
     * Retrieve data from table
     *
     * @param string $tableName
     * @param string $linkageColumnName
     * @param array $ids
     * @return array
     */
    private function getDataFromTable($tableName, $linkageColumnName, $ids)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from([$tableName . '_table' => $this->getTable($tableName)])
            ->where($tableName . '_table.' . $linkageColumnName . ' IN (?)', $ids);

        return $connection->fetchAll($select);
    }
}
