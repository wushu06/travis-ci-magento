<?php

namespace Cminds\MultiUserAccounts\Observer\Checkout\Quote;

use Cminds\MultiUserAccounts\Helper\View as ViewHelper;
use Cminds\MultiUserAccounts\Model\Config as ModuleConfig;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\App\RequestInterface;

/**
 * Cminds MultiUserAccounts after order save observer.
 * Will be executed on "checkout_submit_all_after" event.
 *
 * @category Cminds
 * @package  Cminds_MultiUserAccounts
 * @author   Rafal Andryanczyk <rafal.andryanczyk@gmail.com>
 */
class SubmitAllAfter implements ObserverInterface
{
    /**
     * Order sender object.
     *
     * @var OrderSender
     */
    private $orderSender;

    /**
     * Module config object.
     *
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * View helper object.
     *
     * @var ViewHelper
     */
    private $viewHelper;

    /**
     * Customer session object.
     *
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Customer factory object.
     *
     * @var CustomerFactory
     */
    private $customerFactory;
    
    /**
     * RequestInterface object
     *
     * @var RequestInterface
     */
    private $request;
    /**
     * SubmitAllAfter constructor.
     *
     * @param OrderSender     $orderSender Order sender object.
     * @param ModuleConfig    $moduleConfig Module config object.
     * @param ViewHelper      $viewHelper View helper object.
     * @param CustomerSession $customerSession Customer session object.
     * @param CustomerFactory $customerFactory Customer factory object.
     * @param RequestInterface $request RequestInterface object.
     */
    public function __construct(
        OrderSender $orderSender,
        ModuleConfig $moduleConfig,
        ViewHelper $viewHelper,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        RequestInterface $request
    ) {
        $this->orderSender = $orderSender;
        $this->moduleConfig = $moduleConfig;
        $this->viewHelper = $viewHelper;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->request = $request;
    }

    /**
     * Check permission to send order confirmation mail.
     *
     * @param Observer $observer Observer object.
     *
     * @return SubmitAllAfter
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isEnabled() === false) {
            return $this;
        }

        $checkCheckoutProcess = $this->request->getPostValue();
        // Checkout with Multiple addresses
        if (!empty($checkCheckoutProcess)) {

            $orders = $observer->getEvent()->getOrders();
            /** @var  \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getEvent()->getQuote();

            foreach($orders as $key=>$order) {
        
                //@TODO check for nested
                if ($this->viewHelper->isSubaccountLoggedIn() === false) {
                    // code from Magento\Quote\Observer\Webapi\SubmitOrder
                    /**
                     * a flag to set that there will be redirect to third party after confirmation
                     */
                    $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();

                    // fix for admin order creation - email order confirmation checkbox
                    $requestData = $this->request->getParams();
                    $canSend = true;
                    if (count($requestData) && isset($requestData['order']) && !isset($requestData['order']['send_confirmation'])) {
                        $canSend = false;
                    }

                    if (!$redirectUrl && $order->getCanSendNewEmailFlag() && $canSend) {
                        $this->orderSender->send($order);
                    }

                    return $this;
                }

                $subaccountDataObject = $this->customerSession->getSubaccountData();

                $customerMaster = $this->customerFactory->create()
                    ->load($subaccountDataObject->getParentCustomerId());
                $customerSubaccount = $this->customerFactory->create()
                    ->load($subaccountDataObject->getCustomerId());

                $notificationConfig = $this->moduleConfig->getNotificationConfig();

                $originalCustomerFirstname = $order->getCustomerFirstname();
                $originalCustomerLastName = $order->getCustomerLastname();
                $originalCustomerEmail = $order->getCustomerEmail();

                switch ($notificationConfig) {
                    case ModuleConfig::NOTIFICATION_MAIN_ACCOUNT:
                        $order
                            ->setCustomerFirstname($customerMaster->getFirstname())
                            ->setCustomerLastname($customerMaster->getLastname())
                            ->setCustomerEmail($customerMaster->getEmail());
                        $this->orderSender->send($order);
                        break;
                    case ModuleConfig::NOTIFICATION_SUBACCOUNT:
                        if ($subaccountDataObject->getCheckoutOrderPlacedNotificationPermission()) {
                            $order
                                ->setCustomerFirstname($customerSubaccount->getFirstname())
                                ->setCustomerLastname($customerSubaccount->getLastname())
                                ->setCustomerEmail($customerSubaccount->getEmail());
                            $this->orderSender->send($order);
                        }
                        break;
                    case ModuleConfig::NOTIFICATION_BOTH:
                        $order
                            ->setCustomerFirstname($customerMaster->getFirstname())
                            ->setCustomerLastname($customerMaster->getLastname())
                            ->setCustomerEmail($customerMaster->getEmail());
                        $this->orderSender->send($order);

                        if ($subaccountDataObject->getCheckoutOrderPlacedNotificationPermission()) {
                            $order
                                ->setCustomerFirstname($customerSubaccount->getFirstname())
                                ->setCustomerLastname($customerSubaccount->getLastname())
                                ->setCustomerEmail($customerSubaccount->getEmail());
                            $this->orderSender->send($order);
                        }
                        break;
                }

                // rollback original order data
                $order
                    ->setCustomerFirstname($originalCustomerFirstname)
                    ->setCustomerLastname($originalCustomerLastName)
                    ->setCustomerEmail($originalCustomerEmail)
                    ->save();

                $quote
                    ->setIsApproved(0)
                    ->setSubaccountId(null)
                    ->save();

                return $this;
            }
        } else {

            $order = $observer->getEvent()->getOrder();
            /** @var  \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getEvent()->getQuote();

            //@TODO check for nested
            if ($this->viewHelper->isSubaccountLoggedIn() === false) {
                // code from Magento\Quote\Observer\Webapi\SubmitOrder
                /**
                 * a flag to set that there will be redirect to third party after confirmation
                 */
                $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();

                // fix for admin order creation - email order confirmation checkbox
                $requestData = $this->request->getParams();
                $canSend = true;
                if (count($requestData) && isset($requestData['order']) && !isset($requestData['order']['send_confirmation'])) {
                    $canSend = false;
                }

                if (!$redirectUrl && $order->getCanSendNewEmailFlag() && $canSend) {
                    $this->orderSender->send($order);
                }

                return $this;
            }

            $subaccountDataObject = $this->customerSession->getSubaccountData();

            $customerMaster = $this->customerFactory->create()
                ->load($subaccountDataObject->getParentCustomerId());
            $customerSubaccount = $this->customerFactory->create()
                ->load($subaccountDataObject->getCustomerId());

            $notificationConfig = $this->moduleConfig->getNotificationConfig();

            $originalCustomerFirstname = $order->getCustomerFirstname();
            $originalCustomerLastName = $order->getCustomerLastname();
            $originalCustomerEmail = $order->getCustomerEmail();

            switch ($notificationConfig) {
                case ModuleConfig::NOTIFICATION_MAIN_ACCOUNT:
                    $order
                        ->setCustomerFirstname($customerMaster->getFirstname())
                        ->setCustomerLastname($customerMaster->getLastname())
                        ->setCustomerEmail($customerMaster->getEmail());
                    $this->orderSender->send($order);
                    break;
                case ModuleConfig::NOTIFICATION_SUBACCOUNT:
                    if ($subaccountDataObject->getCheckoutOrderPlacedNotificationPermission()) {
                        $order
                            ->setCustomerFirstname($customerSubaccount->getFirstname())
                            ->setCustomerLastname($customerSubaccount->getLastname())
                            ->setCustomerEmail($customerSubaccount->getEmail());
                        $this->orderSender->send($order);
                    }
                    break;
                case ModuleConfig::NOTIFICATION_BOTH:
                    $order
                        ->setCustomerFirstname($customerMaster->getFirstname())
                        ->setCustomerLastname($customerMaster->getLastname())
                        ->setCustomerEmail($customerMaster->getEmail());
                    $this->orderSender->send($order);

                    if ($subaccountDataObject->getCheckoutOrderPlacedNotificationPermission()) {
                        $order
                            ->setCustomerFirstname($customerSubaccount->getFirstname())
                            ->setCustomerLastname($customerSubaccount->getLastname())
                            ->setCustomerEmail($customerSubaccount->getEmail());
                        $this->orderSender->send($order);
                    }
                    break;
            }

            // rollback original order data
            $order
                ->setCustomerFirstname($originalCustomerFirstname)
                ->setCustomerLastname($originalCustomerLastName)
                ->setCustomerEmail($originalCustomerEmail)
                ->save();

            $quote
                ->setIsApproved(0)
                ->setSubaccountId(null)
                ->save();

            return $this;
        }
    }
}