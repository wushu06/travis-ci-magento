<?php


namespace Elementary\EmployeesManager\Api\Data;

/**
 * Interface CustomerEmployeeSearchResultsInterface
 *
 * @package Elementary\EmployeesManager\Api\Data
 */
interface CustomerEmployeeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get CustomerEmployee list.
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface[]
     */
    public function getItems();

    /**
     * Set title list.
     * @param \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

