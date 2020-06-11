<?php

namespace Elementary\EmployeesManager\Block\CustomerEmployee;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 */
class FormCustomerEmployee extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;
    /**
     * @var \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\CollectionFactory
     */
    private $collectionFactory;

    private $customerEmployees;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param $imageBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     *
     * @return string
     */

    public function searchUrl()
    {
        return  $this->getUrl('rest/V1/elementary-employeesmanager/customeremployee/search');
    }

    /**
     *
     * @return string
     */
    public function formUrl()
    {
        return  'rest/V1/elementary-employeesmanager/customeremployee';
    }

    /**
     *
     * @return string
     */

    public function orderUrl()
    {
        return  $this->getUrl('employees/customeremployee/orders');
    }
    /**
     *
     * @return boolean
     */
    public function showSelector(): bool
    {
        $category = $this->coreRegistry->registry('current_category');
        return $category->getId() && $category->getName() == 'Personal Pack';
    }
    /**
     *
     * @return object
     */
    public function getEmployees()
    {
        if (is_null($this->customerEmployees)) {
            $collection = $this->collectionFactory->create();
            $this->customerEmployees =  $collection->addAttributeToSelect('*');
        }
        return $this->customerEmployees;
    }
}
