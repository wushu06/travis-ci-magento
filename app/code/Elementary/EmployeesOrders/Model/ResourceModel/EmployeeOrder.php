<?php
namespace Elementary\EmployeesOrders\Model\ResourceModel;

class EmployeeOrder extends AbstractModel
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('elementary_employees_manager_employee_order', 'employee_order_id');
    }
}
