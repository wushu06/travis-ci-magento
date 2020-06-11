<?php


namespace Elementary\EmployeesManager\Api\Data;

/**
 * Interface CustomerEmployeeInterface
 *
 * @package Elementary\EmployeesManager\Api\Data
 */
interface CustomerEmployeeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ENTITY_ID = 'entity_id';
    const ID = 'entity_id';
    const NAME = 'name';
    const PRINTED_NAME = 'printed_name';
    const STATUS = 'status';
    const DISPLAY_AREA = 'display_area';
    const COMMENT = 'comment';
    const CUSTOMER_ID = 'customer_id';
    const GROUP_ID = 'group_id';
    const CREATED_AT = 'created_at';
    const STORE_IDS = 'store_ids';
    const STATUS_DISABLED = 2;
    const STATUS_ENABLED = 1;

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Elementary\EmployeesManager\Api\Data\CustomerEmployeeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Elementary\EmployeesManager\Api\Data\CustomerEmployeeExtensionInterface $extensionAttributes
    );

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface
     */
    public function setEntityId($entityId);
    /**
     * @return int
     */
    public function getId();


    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $value
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return string
     */
    public function getDisplayArea();

    /**
     * @param string $value
     * @return $this
     */
    public function setDisplayArea($value);


    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $value
     * @return $this
     */
    public function setName($value);

    /**
     * @return string
     */
    public function getPrintedName();

    /**
     * @param string $value
     * @return $this
     */
    public function setPrintedName($value);

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param string $value
     * @return $this
     */
    public function setComment($value);

    /**
     * @return string
     */
    public function getCustomerId();

    /**
     * @param string $value
     * @return $this
     */
    public function setCustomerId($value);

    /**
     * @return string
     */
    public function getGroupId();

    /**
     * @param string $value
     * @return $this
     */
    public function setGroupId($value);

    /**
     * @return mixed
     */
    public function getStoreIds();

    /**
     * @param mixed $value
     * @return $this
     */
    public function setStoreIds(array $value);

}

