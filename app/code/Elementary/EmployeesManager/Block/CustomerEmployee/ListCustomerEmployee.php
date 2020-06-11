<?php

namespace Elementary\EmployeesManager\Block\CustomerEmployee;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface;
use Elementary\EmployeesManager\Model\CustomerEmployee;
use Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\CollectionFactory as CustomerEmployeeCollectionFactory;
use Elementary\EmployeesManager\Model\CustomerEmployee\Url;

/**
 * @api
 */
class ListCustomerEmployee extends Template
{
    /**
     * @var CustomerEmployeeCollectionFactory
     */
    private $customerEmployeeCollectionFactory;
    /**
     * @var \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\Collection
     */
    private $customerEmployees;
    /**
     * @var Url
     */
    private $urlModel;
    /**
     * @var \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface
     */
    private $employeeRepository;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var CustomerEmployeeCollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param CustomerEmployeeCollectionFactory $customerEmployeeCollectionFactory
     * @param Url $urlModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerEmployeeCollectionFactory $customerEmployeeCollectionFactory,
        \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface $employeeRepository,
       \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\CollectionFactory $collectionFactory,

        Url $urlModel,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->customerEmployeeCollectionFactory = $customerEmployeeCollectionFactory;
        $this->urlModel = $urlModel;
        parent::__construct($context, $data);
        $this->employeeRepository = $employeeRepository;
        $this->objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\Collection
     */
    public function getCustomerEmployees()
    {
        if (is_null($this->customerEmployees)) {
            $collection = $this->collectionFactory->create();
            $this->customerEmployees =  $collection->addAttributeToSelect('*');
        }
        return $this->customerEmployees;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getLayout()->createBlock(Pager::class, 'elementary.employees_manager.customer_employee.list.pager');
        $pager->setCollection($this->getCustomerEmployees());
        $this->setChild('pager', $pager);
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param CustomerEmployeeInterface $customerEmployee
     * @return string
    */
    public function getCustomerEmployeeUrl($customerEmployee)
    {
        return $this->urlModel->getCustomerEmployeeUrl($customerEmployee);
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
}
