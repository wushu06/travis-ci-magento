<?php
namespace ED\Payment\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class OrderLoadAfter
 * @package ED\Order\Observer\Sales
 */
class OrderLoadAfter implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * DisabledByAttribute constructor.
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerSession = $customerSession;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();

        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->getOrderExtensionDependency();
        }

        $customer = $this->customerRepositoryInterface->getById($order->getCustomerId());

        if ($credit = $customer->getCustomAttribute('credit_on_account')) {
            $credit = $credit->getValue();
        }
        $extensionAttributes->setCreditOnAccount($credit);
        $order->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return mixed
     */
    private function getOrderExtensionDependency()
    {
        $orderExtension = \Magento\Framework\App\ObjectManager::getInstance()->get(
            '\Magento\Sales\Api\Data\OrderExtension'
        );

        return $orderExtension;
    }
}
