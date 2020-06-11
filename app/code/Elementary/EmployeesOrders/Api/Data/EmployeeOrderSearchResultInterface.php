<?php
namespace Elementary\EmployeesOrders\Api\Data;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface EmployeeOrderSearchResultInterface
{
    /**
     * get items
     *
     * @return \Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $count
     * @return $this
     */
    public function setTotalCount($count);
}
