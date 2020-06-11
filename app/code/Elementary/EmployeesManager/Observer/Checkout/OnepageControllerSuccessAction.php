<?php


namespace Elementary\EmployeesManager\Observer\Checkout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Serialize\Serializer\Json;
use Elementary\EmployeesOrders\Api\EmployeeOrderRepositoryInterface;
use Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface;
use Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterfaceFactory;

/**
 * Class OnepageControllerSuccessAction
 *
 * @package Elementary\EmployeesManager\Observer\Checkout
 */
class OnepageControllerSuccessAction implements \Magento\Framework\Event\ObserverInterface
{

    protected $serializer;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Selection\Employee\Api\Repository\EmployeeRepositoryInterface
     */
    private $employeeRepository;
    /**
     * @var \Elementary\EmployeesManager\Model\CustomerEmployee
     */
    private $employee;
    /**
     * @var EmployeeOrderInterfaceFactory
     */
    private $employeeOrderFactory;
    /**
     * @var EmployeeOrderRepositoryInterface
     */
    private $employeeOrderRepository;

    public function __construct(
        \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface $employeeRepository,
        \Elementary\EmployeesManager\Model\CustomerEmployee $employee,
        EmployeeOrderInterfaceFactory $employeeOrderFactory,
        EmployeeOrderRepositoryInterface $employeeOrderRepository,
        Json $serializer = null

    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->employeeRepository = $employeeRepository;
        $this->employee = $employee;
        $this->employeeOrderFactory = $employeeOrderFactory;
        $this->employeeOrderRepository = $employeeOrderRepository;
    }
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            $objectManager = ObjectManager::getInstance();
            $logger = $objectManager->create('Psr\Log\LoggerInterface');
            $names = [];
            foreach ($order->getAllVisibleItems() as $item) {
                if($item->getBuyRequest()->getEmployee()) {
                    $names[$item->getId()]['employee'] = array(
                        "label" => "Employee name",
                        "value" => $item->getBuyRequest()->getEmployee()
                    );
                }

            }
            $orderId = $order->getId();

            if (!empty($names)) {
                $this->addAdditionalOptionToOrder($order, $names);
                $this->updateEmployeesWithOrderId($orderId, $names);
            }
        } catch (Exception $e) {
            // catch error if any
            $logger->error($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $order
     * @param $array
     */
    protected function addAdditionalOptionToOrder($order, $array)
    {
        foreach ($order->getAllVisibleItems() as $orderItem) {
            if(array_key_exists($orderItem->getId(), $array)) {
                $options = $orderItem->getProductOptions();
                $options['additional_options'] = $array[$orderItem->getId()];
                $orderItem->setProductOptions($options);
                $orderItem->save();
            }
        }
    }

    /**
     * @param $itemId
     * @param $name
     */
    protected function updateEmployeesWithItemId($itemId, $name)
    {
        $blockInstance = $this->getEmployeeByName($name);
        foreach ($blockInstance as $employee) {
            if ($employee->getId()) {
                $data['entity_id'] = $employee->getId();
                $data['item_id'] = $itemId;

                $model = $this->employeeRepository->create();
                $model->setData($data)
                    ->save();
            }
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function getEmployeeByName($name)
    {
        return $this->employee->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('name', ['eq' => $name])->load();
    }

    /**
     * @param $orderId
     * @param $names
     */
    protected function updateEmployeesWithOrderId($orderId, $names)
    {

        foreach ($names as $key=>$value) {
            $blockInstance = $this->getEmployeeByName($value['employee']['value']);
            foreach ($blockInstance as $employee) {
                if ($employeeId = $employee->getId()) {
                    $employeeOrder = $this->employeeOrderFactory->create();
                    $data['employee_id'] = $employeeId;
                    $data['order_id'] = $orderId;
                    $data['item_id'] = $key;
                    $employeeOrder->setData($data)
                        ->save();
                }
            }
        }
    }
}

