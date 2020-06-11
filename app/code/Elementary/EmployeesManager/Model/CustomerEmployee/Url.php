<?php

namespace Elementary\EmployeesManager\Model\CustomerEmployee;

use Magento\Framework\UrlInterface;
use Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface;

class Url
{
    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getListUrl()
    {
        return $this->urlBuilder->getUrl('elementary_employees_manager/customeremployee/index');
    }

    /**
     * @param CustomerEmployeeInterface $customerEmployee
     * @return string
     */
    public function getCustomerEmployeeUrl($customerEmployee)
    {
        return $this->urlBuilder->getUrl('elementary_employees_manager/customeremployee/view', ['id' => $customerEmployee->getId()]);
    }
}
