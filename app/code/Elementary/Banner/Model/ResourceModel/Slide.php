<?php

namespace Elementary\Banner\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Elementary\Banner\Model;

/**
 * Slide Resource Model
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Slide extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Model\Slide::TABLE, Model\Slide::SLIDE_ID);
    }

    /**
     * Get Customer Group Ids for slide
     *
     * @param int $slideId
     *
     * @return int[]
     */
    public function getCustomerGroups($slideId)
    {
        $customerGroups = [];
        $connection = $this->getConnection();
        $select = $connection->select()->from([
            'customer_groups' => $this->getTable(Model\Slide::TABLE_CUSTOMER_GROUP)
        ], 'customer_group');

        $select->where('customer_groups.slide_id = :slide_id');
        $rows = $connection->fetchAssoc($select, [
            'slide_id' => (int) $slideId,
        ]);

        foreach ($rows as $row) {
            $customerGroups[] = (int) $row['customer_group'];
        }

        return $customerGroups;
    }

    /**
     * Save customer groups to slides
     *
     * @param AbstractModel $object
     *
     * @return $this|AbstractDb
     */
    protected function _afterSave(AbstractModel $object)
    {
        $customerGroupIds = $object->getData('customer_group');
        if (!$customerGroupIds) {
            $customerGroupIds = [
                32000
            ];
        }

        $this->getConnection()->delete(Model\Slide::TABLE_CUSTOMER_GROUP, [
            'slide_id = ?' => (int) $object->getId(),
        ]);

        if (!$customerGroupIds) {
            return $this;
        }

        $inserts = [];
        foreach ($customerGroupIds as $customerGroupId) {
            $inserts[] = [
                Model\Slide::SLIDE_ID => (int) $object->getId(),
                'customer_group'      => (int) $customerGroupId,
            ];
        }

        $this->getConnection()->insertMultiple(Model\Slide::TABLE_CUSTOMER_GROUP, $inserts);

        return $this;
    }

    /**
     * Add customer groups to slides
     *
     * @param AbstractModel $object
     *
     * @return $this|AbstractDb
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);

        $groupIds = [];
        $connection = $this->getConnection();
        $bind = [
            ':slide_id' => (int)$object->getId()
        ];

        $select = $connection->select()
            ->from($this->getTable(Model\Slide::TABLE_CUSTOMER_GROUP), ['customer_group'])
            ->where('slide_id=:slide_id');

        $results = $connection->fetchAll($select, $bind);
        if (count($results)) {
            foreach ($results as $result) {
                $groupIds[] = (int) $result['customer_group'];
            }
        } else {
            $groupIds[] = 32000;
        }

        $object->setData('customer_group', $groupIds);

        return $this;
    }
}
