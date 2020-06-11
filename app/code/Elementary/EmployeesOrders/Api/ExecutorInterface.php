<?php
namespace Elementary\EmployeesOrders\Api;

/**
 * @api
 */
interface ExecutorInterface
{
    /**
     * execute
     * @param int $id
     */
    public function execute($id);
}
