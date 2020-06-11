<?php
namespace ED\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class DisabledByAttribute
 * @package ED\Payment\Observer
 */
class DisabledByAttribute implements ObserverInterface
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
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $customer = $this->customerRepositoryInterface->getById($customerId);
        $customerAttributeData = $customer->__toArray();
        $customerAttributeData = $customerAttributeData['custom_attributes'];
        if (array_key_exists('credit_on_account', $customerAttributeData)) {
            $credit = $customerAttributeData['credit_on_account']['value'];
            if ($credit == 0 && $observer->getEvent()->getMethodInstance()->getCode()=="cashondelivery") {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
            }
        }
    }
}
