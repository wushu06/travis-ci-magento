<?php
namespace Elementary\EmployeesOrders\Model;

use Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface;
use Magento\Framework\Model\AbstractModel;
use Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder as EmployeeOrderResourceModel;

/**
 * @method \Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder _getResource()
 * @method \Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder getResource()
 */
class EmployeeOrder extends AbstractModel implements EmployeeOrderInterface
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'elementary_employeesorders_employee_order';
    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'elementary_employeesorders_employee_order';
    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'employee_order';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EmployeeOrderResourceModel::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Page id
     *
     * @return array
     */
    public function getEmployeeOrderId()
    {
        return $this->getData(EmployeeOrderInterface::EMPLOYEE_ORDER_ID);
    }

    /**
     * set Employee Order id
     *
     * @param  int $employeeOrderId
     * @return EmployeeOrderInterface
     */
    public function setEmployeeOrderId($employeeOrderId)
    {
        return $this->setData(EmployeeOrderInterface::EMPLOYEE_ORDER_ID, $employeeOrderId);
    }

    /**
     * @param int $employeeId
     * @return EmployeeOrderInterface
     */
    public function setEmployeeId($employeeId)
    {
        return $this->setData(EmployeeOrderInterface::EMPLOYEE_ID, $employeeId);
    }

    /**
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->getData(EmployeeOrderInterface::EMPLOYEE_ID);
    }

    /**
     * @param int $orderId
     * @return EmployeeOrderInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(EmployeeOrderInterface::ORDER_ID, $orderId);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(EmployeeOrderInterface::ORDER_ID);
    }

    /**
     * @param int $itemId
     * @return EmployeeOrderInterface
     */
    public function setItemId($itemId)
    {
        return $this->setData(EmployeeOrderInterface::ITEM_ID, $itemId);
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->getData(EmployeeOrderInterface::ITEM_ID);
    }

    /**
     * @param string $name
     * @return EmployeeOrderInterface
     */
    public function setName($name)
    {
        return $this->setData(EmployeeOrderInterface::NAME, $name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(EmployeeOrderInterface::NAME);
    }

    /**
     * @param int $isActive
     * @return EmployeeOrderInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(EmployeeOrderInterface::IS_ACTIVE, $isActive);
    }

    /**
     * @return int
     */
    public function getIsActive()
    {
        return $this->getData(EmployeeOrderInterface::IS_ACTIVE);
    }
}
