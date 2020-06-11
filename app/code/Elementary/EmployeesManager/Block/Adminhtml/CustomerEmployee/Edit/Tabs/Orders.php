<?php

namespace Elementary\EmployeesManager\Block\Adminhtml\Customeremployee\Edit\Tabs;

use Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml customer recent orders grid block
 */
class Orders extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    private $_resource;
    private $_connection;
    /**
     * @var CustomerEmployeeRepositoryInterface
     */
    private $employeeRepositoryInterface;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var CustomerEmployeeRepositoryInterface
     */
    private $employeeRepository;
    /**
     * @var \Elementary\EmployeesOrders\Model\EmployeeOrderFactory
     */
    private $employeeOrderFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
       // \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface $employeeRepository,
        \Elementary\EmployeesOrders\Model\EmployeeOrderFactory $employeeOrderFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\App\ResourceConnection $Resource,
        CustomerEmployeeRepositoryInterface $employeeRepositoryInterface,
        array $data = []
    ) {
        $this->_resource = $Resource;
        $this->_connection = $this->_resource->getConnection();
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        $this->customerRepository = $customerRepository;
        $this->employeeRepositoryInterface = $employeeRepositoryInterface;
        parent::__construct($context, $backendHelper, $data);
        $this->employeeRepository = $employeeRepository;
        $this->employeeOrderFactory = $employeeOrderFactory;
    }

    /**
     * Initialize the orders grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('employees_order_grid');
        $this->setDefaultSort('entity_id', 'desc');
        $this->setSortable(true);
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize(10)->setCurPage(1);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        $orderData = [];
        if ($collectionByName = $this->getEmployeeOrderById($id)) {
            foreach ($collectionByName as $collection) {
                $orderId = $collection->getOrderId();
                $orderData[] = $orderId;
            }

        }
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $orderData]);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',

            ['header' => __('ID'), 'index' => 'entity_id', 'type' => 'number', 'width' => '100px']
        );

        $this->addColumn(
            'increment_id',
            [
                'header' => __('Increment id'),
                'index' => 'increment_id',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
            ]
        );
        $this->addColumn(
            'total_due',
            [
                'header' => __('Total'),
                'index' => 'total_due',
            ]
        );
        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'index' => 'customer_email',
            ]
        );


        return parent::_prepareColumns();
    }





    /**
     * @param $employeeId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function getEmployeeOrderById($employeeId)
    {
        return $this->employeeOrderFactory->create()->getCollection()
            ->addFieldToFilter('employee_id', $employeeId);
    }
}

