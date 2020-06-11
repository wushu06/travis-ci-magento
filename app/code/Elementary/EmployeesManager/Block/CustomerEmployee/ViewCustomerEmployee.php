<?php

namespace Elementary\EmployeesManager\Block\CustomerEmployee;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class ViewCustomerEmployee extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param $imageBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * get current Customer Employee
     *
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface
     */
    public function getCurrentCustomerEmployee()
    {
        return $this->coreRegistry->registry('current_customer_employee');
    }
}
