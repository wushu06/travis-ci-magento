<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Model\ResourceModel;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Type;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Elementary\EmployeesManager\Setup\CustomerEmployeeSetup;

class Attribute extends EavAttribute {
    /**
     * Eav config
     *
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @param Context               $context        [description]
     * @param StoreManagerInterface $storeManager   [description]
     * @param Type                  $eavEntityType  [description]
     * @param Config                $eavConfig      [description]
     * @param string                $connectionName [description]
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Type $eavEntityType,
        Config $eavConfig,
        $connectionName = null
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $storeManager, $eavEntityType, $connectionName);
    }

    /**
     * Trigger actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {
        $applyTo = $object->getApplyTo();
        if (is_array($applyTo)) {
            $object->setApplyTo(implode(',', $applyTo));
        }
        return parent::_beforeSave($object);
    }

    /**
     * Trigger actions after object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object) {
        $this->_clearUselessAttributeValues($object);
        return parent::_afterSave($object);
    }

    /**
     * Clear useless attribute values
     *
     * @param  AbstractModel $object
     * @return $this
     */
    protected function _clearUselessAttributeValues(AbstractModel $object) {
        $origData = $object->getOrigData();

        if ($object->isScopeGlobal() && isset(
                $origData['is_global']
            ) && ScopedAttributeInterface::SCOPE_GLOBAL != $origData['is_global']
        ) {
            $attributeStoreIds = array_keys($this->_storeManager->getStores());
            if (!empty($attributeStoreIds)) {
                $delCondition = [
                    'attribute_id = ?' => $object->getId(),
                    'store_id IN(?)' => $attributeStoreIds,
                ];
                $this->getConnection()->delete($object->getBackendTable(), $delCondition);
            }
        }

        return $this;
    }

    /**
     * Delete entity
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteEntity(AbstractModel $object) {
        if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $select = $this->getConnection()->select()->from(
            $this->getTable('eav_entity_attribute')
        )->where(
            'entity_attribute_id = ?',
            (int) $object->getEntityAttributeId()
        );
        $result = $this->getConnection()->fetchRow($select);

        if ($result) {
            $attribute = $this->_eavConfig->getAttribute(
                CustomerEmployeeSetup::ENTITY_TYPE_CODE,
                $result['attribute_id']
            );

            $backendTable = $attribute->getBackend()->getTable();
            if ($backendTable) {
                $select = $this->getConnection()->select()->from(
                    $attribute->getEntity()->getEntityTable(),
                    'entity_id'
                )->where(
                    'attribute_set_id = ?',
                    $result['attribute_set_id']
                );

                $clearCondition = [
                    'attribute_id =?' => $attribute->getId(),
                    'entity_id IN (?)' => $select,
                ];
                $this->getConnection()->delete($backendTable, $clearCondition);
            }
        }

        $condition = ['entity_attribute_id = ?' => $object->getEntityAttributeId()];
        $this->getConnection()->delete($this->getTable('eav_entity_attribute'), $condition);

        return $this;
    }
}