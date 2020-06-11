<?php


namespace Elementary\EmployeesManager\Model\Data;

use Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface;

/**
 * Class CustomerEmployee
 *
 * @package Elementary\EmployeesManager\Model\Data
 */
class CustomerEmployee extends \Magento\Framework\Api\AbstractExtensibleObject implements CustomerEmployeeInterface
{

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }
 /**
     * Get entity_id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }
    /**
     * Set entity_id
     * @param string $entityId
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }


    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Elementary\EmployeesManager\Api\Data\CustomerEmployeeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Elementary\EmployeesManager\Api\Data\CustomerEmployeeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrintedName()
    {
        return $this->_get(self::PRINTED_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrintedName($value)
    {
        return $this->setData(self::PRINTED_NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }
    /**
     * {@inheritdoc}
     */
    public function getDisplayArea()
    {
        return $this->_get(self::DISPLAY_AREA);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayArea($value)
    {
        return $this->setData(self::DISPLAY_AREA, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->_get(self::COMMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($value)
    {
        return $this->setData(self::COMMENT, $value);
    }


    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($value)
    {
        return $this->setData(self::CUSTOMER_ID, $value);
    }


    /**
     * {@inheritdoc}
     */
    public function getGroupId()
    {
        return $this->_get(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupId($value)
    {
        return $this->setData(self::GROUP_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreIds()
    {
        return $this->_get(self::STORE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreIds($value)
    {
        return $this->setData(self::STORE_IDS, $value);
    }

}

