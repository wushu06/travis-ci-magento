<?php
namespace Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder;

use Elementary\EmployeesOrders\Model\EmployeeOrder;
use Elementary\EmployeesOrders\Model\ResourceModel\AbstractCollection;

/**
 * @api
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            EmployeeOrder::class,
            \Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder::class
        );
    }
}
