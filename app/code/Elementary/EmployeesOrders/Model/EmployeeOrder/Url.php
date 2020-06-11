<?php

namespace Elementary\EmployeesOrders\Model\EmployeeOrder;

use Magento\Framework\UrlInterface;
use Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface;

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
        return $this->urlBuilder->getUrl('elementary_employees_manager/employeeorder/index');
    }

    /**
     * @param EmployeeOrderInterface $employeeOrder
     * @return string
     */
    public function getEmployeeOrderUrl(EmployeeOrderInterface $employeeOrder)
    {
        return $this->urlBuilder->getUrl('elementary_employees_manager/employeeorder/view', ['id' => $employeeOrder->getId()]);
    }
}
