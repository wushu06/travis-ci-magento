<?php
namespace Elementary\EmployeesOrders\Api\Data;

/**
 * @api
 */
interface EmployeeOrderInterface
{
    const EMPLOYEE_ORDER_ID = 'employee_order_id';
    const EMPLOYEE_ID = 'employee_id';
    const ORDER_ID = 'order_id';
    const ITEM_ID = 'item_id';
    const NAME = 'name';
    /**
     * @var string
     */
    const IS_ACTIVE = 'is_active';
    /**
     * @var int
     */
    const STATUS_ENABLED = 1;
    /**
     * @var int
     */
    const STATUS_DISABLED = 2;
    /**
     * @param int $id
     * @return EmployeeOrderInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return EmployeeOrderInterface
     */
    public function setEmployeeOrderId($id);

    /**
     * @return int
     */
    public function getEmployeeOrderId();

    /**
     * @param int $employeeId
     * @return EmployeeOrderInterface
     */
    public function setEmployeeId($employeeId);

    /**
     * @return int
     */
    public function getEmployeeId();
    /**
     * @param int $orderId
     * @return EmployeeOrderInterface
     */
    public function setOrderId($orderId);

    /**
     * @return int
     */
    public function getOrderId();
    /**
     * @param int $itemId
     * @return EmployeeOrderInterface
     */
    public function setItemId($itemId);

    /**
     * @return int
     */
    public function getItemId();
    /**
     * @param string $name
     * @return EmployeeOrderInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();
    /**
     * @param int $isActive
     * @return EmployeeOrderInterface
     */
    public function setIsActive($isActive);

    /**
     * @return int
     */
    public function getIsActive();
}
