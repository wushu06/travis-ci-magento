<?php

namespace Elementary\EmployeesManager\Controller\CustomerEmployee;

use DateTime;
use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;

class Orders extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var
     */
    protected $_emailNotification;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;
    /**
     * @var
     */
    protected $_connection;
    /**
     * @var \Selection\Employee\Api\Repository\EmployeeRepositoryInterface
     */
    private $employeeRepository;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $date;
    /**
     * @var \Magento\Framework\Escaper
     */
    private $_escaper;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory
     */
    private $orderAddress;
    /**
     * @var \Selection\Employee\Api\Repository\EmployeeOrderRepositoryInterface
     */
    private $employeeOrderRepository;
    /**
     * @var \Selection\Employee\Model\EmployeeOrderFactory
     */
    private $employeeOrderFactory;
    /**
     * @var \Elementary\EmployeesManager\Helper\View
     */
    private $helper;

    /**
     * Orders constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Selection\Employee\Api\Repository\EmployeeRepositoryInterface $employeeRepository,
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Framework\Escaper $_escaper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface $employeeRepository,
        \Elementary\EmployeesOrders\Api\EmployeeOrderRepositoryInterface $employeeOrderRepository,
        \Elementary\EmployeesOrders\Model\EmployeeOrderFactory $employeeOrderFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $orderAddress,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Elementary\EmployeesManager\Helper\View $helper,
        \Magento\Framework\Escaper $_escaper,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->employeeRepository = $employeeRepository;
        $this->order = $order;
        $this->date = $date;
        $this->_escaper = $_escaper;
        $this->_resourceConnection = $resourceConnection;
        $this->_connection = $this->_resourceConnection->getConnection();
        $this->orderAddress = $orderAddress;
        $this->employeeOrderRepository = $employeeOrderRepository;
        $this->employeeOrderFactory = $employeeOrderFactory;
        $this->helper = $helper;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        /* @var Json $result */
        $resultJson = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getParams();
        $id = (int)$this->getRequest()->getParam('id');
        $orderData = [];
        if(!$this->_isAllowed($id)){
            return $resultJson->setData(['data' => $orderData, 'total' => count($orderData)]);
        }
        if ($collectionByName = $this->getEmployeeOrderById($id)) {
            foreach ($collectionByName as $collection) {
                $orderId = $collection->getOrderId();
                $order = $this->order->load($orderId);
                $orderData[$orderId] = $this->getOrderDetails($order);
               // $orderData[$orderId]['items'] = $this->getOrderItems($order);
            }

        }
        return $resultJson->setData(['data' => $orderData, 'total' => count($orderData)]);

    }

    /**
     * @param $order
     * @return array
     * @throws Exception
     */
    protected function getOrderDetails($order)
    {
        return [
            'id' => $order->getId(),
            'real_order_id' => $order->getRealOrderId(),
            'status' => $order->getStatusLabel(),
            'total' => $order->getTotalDue(),
            'ship' => $order->getShippingAddress() ? $order->getShippingAddress()->getName() : '-',
            'date' => $this->date->date(new DateTime($order->getCreatedAt()))->format('d/m/Y')
        ];
    }

    /**
     * @param object $order
     * @return array
     */
    protected function getOrderItems($order)
    {
        $el = [];
        $items = $order->getAllItems();
        foreach ($items as $key => $item) {
            if ($item->getParentItem()) :
                continue;
            endif;

            $el[$key]['item'] = [
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'qty' => $item->getQtyOrdered(),
                'type' => $item->getProductType()
            ];
            $additionalOptions =  $item->getProductOptions();
            if (array_key_exists('options', $additionalOptions)) {
                foreach ($additionalOptions["options"] as $coption) {
                    $el[$key]['custom_options'] = ['employee' => $coption["value"]];
                }
            }

            if (array_key_exists('additional_options', $additionalOptions)) {
                $el[$key]['custom_options'] =  ['employee' =>  $additionalOptions['additional_options']['employee']['value']];
            }
            if (array_key_exists('bundle_options', $additionalOptions)) {
                foreach ($additionalOptions["bundle_options"] as $option) {
                    $el[$key]['options'][] = [
                        'title' => $option['value'][0]['title'],
                        'qty' => $option['value'][0]['qty'],
                        'price' => $option['value'][0]['price']
                    ];
                }
            }
        }
        return $el;
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
    /**
     * @param $id
     * @return bool
     */
    protected function _isAllowed($id)
    {
        $employee = $this->employeeRepository->get($id);
        if($employee->getId()){
            return $employee->getGroupId() === $this->helper->getGroupId();
        }
        return false;
    }

}
