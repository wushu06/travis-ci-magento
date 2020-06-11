<?php


namespace Elementary\EmployeesManager\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CustomerEmployeeRepositoryInterface
 *
 * @package Elementary\EmployeesManager\Api
 */
interface CustomerEmployeeRepositoryInterface
{
    /**
     * @return mixed
     */
    public function create();

    /**
     * Save CustomerEmployee
     * @param \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface $customerEmployee
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface $customerEmployee
    );

    /**
     * Retrieve CustomerEmployee
     * @param string $entityId
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve CustomerEmployee matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete CustomerEmployee
     * @param \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface $customerEmployee
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface $customerEmployee
    );

    /**
     * Delete CustomerEmployee by ID
     * @param string $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);

}

