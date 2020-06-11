<?php


namespace Elementary\EmployeesManager\Model;

use Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface;
use Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class CustomerEmployee
 *
 * @package Elementary\EmployeesManager\Model
 */
class CustomerEmployee extends \Magento\Framework\Model\AbstractModel
{

    /**
     * cache tag
     */
    const CACHE_TAG = 'elementary_customeremployee';

    /**
     * entity_type_id for save Entity Type ID value
     */
    const KEY_ENTITY_TYPE_ID = 'entity_type_id';

    /**
     * attribute_set_id for save Attribute Set ID value
     */
    const KEY_ATTR_TYPE_ID = 'attribute_set_id';


    /**
     * @var string
     */
    protected $_cacheTag = 'elementary_customeremployee';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'elementary_customeremployee';
    protected $customeremployeeDataFactory;

    protected $dataObjectHelper;
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CustomerEmployeeInterfaceFactory $customeremployeeDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee $resource
     * @param \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CustomerEmployeeInterfaceFactory $customeremployeeDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee $resource,
        \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\Collection $resourceCollection,
        array $data = []
    ) {
        $this->customeremployeeDataFactory = $customeremployeeDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve customeremployee model with customeremployee data
     * @return CustomerEmployeeInterface
     */
    public function getDataModel()
    {
        $customeremployeeData = $this->getData();

        $customeremployeeDataObject = $this->customeremployeeDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customeremployeeDataObject,
            $customeremployeeData,
            CustomerEmployeeInterface::class
        );

        return $customeremployeeDataObject;
    }
}

