<?php
namespace Elementary\EmployeesOrders\Api;

use Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface EmployeeOrderRepositoryInterface
{
    /**
     */
    public function create();
    /**
     * @param EmployeeOrderInterface $EmployeeOrder
     * @return EmployeeOrderInterface
     */
    public function save(EmployeeOrderInterface $EmployeeOrder);

    /**
     * @param $id
     * @return EmployeeOrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Elementary\EmployeesOrders\Api\Data\EmployeeOrderSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param EmployeeOrderInterface $EmployeeOrder
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(EmployeeOrderInterface $EmployeeOrder);

    /**
     * @param int $EmployeeOrderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($EmployeeOrderId);

    /**
     * clear caches instances
     * @return void
     */
    public function clear();
}
