<?php
namespace Elementary\EmployeesOrders\Model\EmployeeOrder\Executor;

use Elementary\EmployeesOrders\Api\EmployeeOrderRepositoryInterface;
use Elementary\EmployeesOrders\Api\ExecutorInterface;

class Delete implements ExecutorInterface
{
    /**
     * @var EmployeeOrderRepositoryInterface
     */
    private $employeeOrderRepository;

    /**
     * Delete constructor.
     * @param EmployeeOrderRepositoryInterface $employeeOrderRepository
     */
    public function __construct(
        EmployeeOrderRepositoryInterface $employeeOrderRepository
    ) {
        $this->employeeOrderRepository = $employeeOrderRepository;
    }

    /**
     * @param int $id
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($id)
    {
        $this->employeeOrderRepository->deleteById($id);
    }
}
