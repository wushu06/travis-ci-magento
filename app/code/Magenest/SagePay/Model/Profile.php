<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Model;

use Magenest\SagePay\Model\ResourceModel\Profile as Resource;
use Magenest\SagePay\Model\ResourceModel\Profile\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magenest\SagePay\Helper\Subscription;

class Profile extends AbstractModel
{
    protected $_eventPrefix = 'profile_';

    protected $customerRepository;
//    protected $_objectManager;
    protected $orderFactory;
//    protected $cartFactory;
    protected $transactionFactory;
    protected $_storeManager;
    protected $productFactory;
//    protected $quote;
//    protected $quoteManagement;
    protected $cartRepositoryInterface;
    protected $cartManagementInterface;
    protected $sagehelper;
    protected $sageLogger;

    public function __construct(
        Context $context,
        Registry $registry,
        Resource $resource,
        Collection $resourceCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magenest\SagePay\Model\TransactionFactory $transactionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        $data = []
    ) {
        $this->sagehelper = $sageHelper;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->transactionFactory = $transactionFactory;
        $this->_storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->orderFactory = $orderFactory;
        $this->customerRepository = $customerRepository;
        $this->sageLogger = $sageLogger;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function reOrder()
    {
        $store = $this->_storeManager->getStore(1);
        $websiteId = $store->getWebsiteId();

        $orderId = $this->getData('order_id');
        /** @var \Magento\Sales\Model\Order $orderModel */
        $orderModel = $this->orderFactory->create();
        $origOrder = $orderModel->loadByIncrementId($orderId);

        $firstTransactionId = $this->getData('transaction_id');
        $res = $this->sendRepeatRequest($firstTransactionId, $origOrder);
        $this->sageLogger->debug(var_export($res, true));
        if (isset($res['statusCode']) && $res['statusCode'] == "0000") {
            /** @var \Magenest\SagePay\Model\Transaction $transModel */
            $transModel = $this->transactionFactory->create();

            $customerId = $origOrder->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $customer->setWebsiteId($websiteId);
            $productId = $origOrder->getAllItems()[0]->getProductId();
            $productSku = $origOrder->getAllItems()[0]->getSku();
            $productQty = $origOrder->getAllItems()[0]->getQtyOrdered();
            $amount = $res['amount']->totalAmount;
            $currencyCode = $res['currency'];

            $transactionId = $res['transactionId'];
            /** @var \Magento\Catalog\Model\Product $product */
//                $product = $this->productFactory->create()->load($productId);
            $product = $this->productFactory->create()->loadByAttribute('sku', $productSku);
            $product->setWebsiteIds(['0', '1']);
            $ccLast4 = $res['paymentMethod']['card']['lastFourDigits'];
            $expMonth = substr($res['paymentMethod']['card']['expiryDate'], 2);
            $expYear = "20" . substr($res['paymentMethod']['card']['expiryDate'], -2);
            $ccType = $res['paymentMethod']['card']['cardType'];

            // Create Order
            $cart_id = $this->cartManagementInterface->createEmptyCart();
            $quote = $this->cartRepositoryInterface->get($cart_id);
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote->setStore($store);
            $quote->setCurrency();
            $quote->save();
            $quote->assignCustomer($customer);
            $quote->addProduct($product, intval($productQty));

            $shippingInput = $this->getOriginShippingInfo($origOrder);
            $paymentInput = $this->getOriginPaymentInfo($origOrder);
            if ($shippingInput) {
                $quote->getShippingAddress()->addData($shippingInput);
            }
            if ($paymentInput) {
                $quote->getBillingAddress()->addData($paymentInput);
            }
            $shippingAddress = $quote->getShippingAddress();

            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($origOrder->getShippingMethod()); //shipping method

            $quote->setPaymentMethod(SagePay::CODE); //payment method

            $quote->setInventoryProcessed(false);

            $quote->getPayment()->importData(['method' => SagePay::CODE, 'is_sage_subscription_payment' => true]);

            $quote->collectTotals()->save();

            $quote = $this->cartRepositoryInterface->get($quote->getId());
            /** @var \Magento\Sales\Model\Order $newOrderModel */
            $newOrderModel = $this->cartManagementInterface->submit($quote);

            $newOrderModel->setEmailSent(0);

            $newOrderId = $newOrderModel->getIncrementId();

            $payment = $newOrderModel->getPayment();

            $payment->setCcLast4($ccLast4);
            $payment->setCcExpMonth($expMonth);
            $payment->setCcExpYear($expYear);
            $payment->setCcType($ccType);

            $payment->setTransactionId($transactionId);
            $payment->setAdditionalInformation("sage_trans_id", $transactionId);
            $payment->setIsTransactionClosed(0);

            $newOrderModel->setPayment($payment);
            $newOrderModel->addStatusHistoryComment("Payment status detail: " . $res['statusDetail']);
            $newOrderModel->addStatusHistoryComment("Payment status: " . $res['status']);
            $payment->registerCaptureNotification($origOrder->getBaseGrandTotal());
            $newOrderModel->save();

            $data = [
                'transaction_id' => $transactionId,
                'transaction_type' => $res['transactionType'],
                'transaction_status' => $res['status'],
                'card_secure' => '',
                'status_detail' => $res['statusDetail'],
                'order_id' => $newOrderId,
                'customer_id' => $newOrderModel->getCustomerId(),
                'is_subscription' => "1"
            ];
            $transModel->setData($data)->save();
            $this->updateProfileRecord($newOrderId);
        } else {
            $this->updateProfileRecord('0');
        }
    }

    /**
     * @param $transId
     * @param \Magento\Sales\Model\Order $order
     * @return array|string
     */
    public function sendRepeatRequest($transId, $order)
    {
//        /** @var \Magenest\SagePay\Helper\SageHelper $dataHelper */
//        $dataHelper = $this->_objectManager->create('Magenest\SagePay\Helper\SageHelper');

        $url = $this->sagehelper->getPiEndpointUrl() . '/transactions';
        $payload = $this->sagehelper->buildRepeatQuery($transId, $order);

        $res = $this->sagehelper->sendRequest($url, $payload);

        return $res;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getOriginShippingInfo($order)
    {
        $address = $order->getShippingAddress();
        if ($address) {
            /** @var \Magento\Sales\Model\Order\Address $address */
            $address = $order->getShippingAddress();
            $streetArr = $address->getStreet();
            $streetFull = $streetArr[0];

            return [
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'city' => $address->getCity(),
                'postcode' => $address->getPostcode(),
                'telephone' => $address->getTelephone(),
                'street' => $streetFull,
                'customer_id' => $order->getCustomerId(),
                'email' => $order->getCustomerEmail(),
                'region' => $address->getRegion(),
                'regionCode' => $address->getRegionCode(),
                'region_id' => $address->getRegionId(),
                'country_id' => $address->getCountryId()
            ];
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getOriginPaymentInfo($order)
    {
        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $order->getBillingAddress();
        if ($address) {
            $streetArr = $address->getStreet();
            $streetFull = $streetArr[0];

            return [
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'city' => $address->getCity(),
                'postcode' => $address->getPostcode(),
                'telephone' => $address->getTelephone(),
                'street' => $streetFull,
                'customer_id' => $order->getCustomerId(),
                'email' => $order->getCustomerEmail(),
                'region' => $address->getRegion(),
                'regionCode' => $address->getRegionCode(),
                'region_id' => $address->getRegionId(),
                'country_id' => $address->getCountryId()
            ];
        }

        return false;
    }
//
//    /**
//     * @param $inputMock
//     * @param \Magento\Quote\Model\Quote $quote
//     */
//    public function saveShippingInfo($inputMock, $quote)
//    {
//        //ShippingInformationManagement
//        $management = $this->_objectManager->create('Magento\Checkout\Model\ShippingInformationManagement');
//
//        /** @var  \Magento\Checkout\Model\ShippingInformation $shippingInfo */
//        $shippingInfo = $this->_objectManager->create('Magento\Checkout\Model\ShippingInformation');
//        $shippingInfo->setShippingMethodCode('flatrate');
//        $shippingInfo->setShippingCarrierCode('flatrate');
//        /** @var \Magento\Quote\Model\Quote\Address $address */
//        $address = $this->_objectManager->create('Magento\Quote\Model\Quote\Address');
//
//        $cartId = $quote->getId();
//        $add = $quote->getShippingAddress();
//
//        foreach ($inputMock as $key => $value) {
//            $add->setData($key, $value);
//        }
//        $shippingInfo->setShippingAddress($add);
//        $shippingInfo->setId(null);
//
//        $management->saveAddressInformation($cartId, $shippingInfo);
//    }
//
//    /**
//     * @param $inputMock
//     * @param \Magento\Quote\Model\Quote $quote
//     */
//    public function savePaymentInfo($inputMock, $quote)
//    {
//        //savePaymentInformationAndPlaceOrder
//
//        /** @var \Magento\Checkout\Model\PaymentInformationManagement $management */
//        $management = $this->_objectManager->create('Magento\Checkout\Model\PaymentInformationManagement');
//        /** @var \Magento\Quote\Model\Quote\Payment $paymentMethod */
//        $paymentMethod = $this->_objectManager->create('Magento\Quote\Model\Quote\Payment');
//        $paymentMethod->setMethod(SagePay::CODE);
//        $paymentMethod->setAdditionalInformation('is_sage_subscription_payment', true);
//        $billingAdd = $quote->getBillingAddress();
//
//        foreach ($inputMock as $key => $value) {
//            $billingAdd->setData($key, $value);
//        }
//
//        $billingAdd->setDataChanges(true);
//        $billingAdd->save();
//        $billingAdd->setId(null);
//        $cartId = $quote->getId();
//
////        return $management->savePaymentInformation($cartId, $paymentMethod, $billingAdd);
//        return $management->savePaymentInformationAndPlaceOrder($cartId, $paymentMethod, $billingAdd);
//    }

    public function updateProfileRecord($newOrderId) {
        $remaining = $this->getData('remaining_cycles');
        $remaining -= 1;
        $lastBilled = date('Y-m-d');
        $nextBilling = date('Y-m-d', strtotime("+ " . $this->getData('frequency')));
        $this->addData([
            'remaining_cycles' => $remaining,
            'last_billed' => $lastBilled,
            'next_billing' => $nextBilling
        ]);
        if ($remaining == 0) {
            $this->addData(['status' => Subscription::SUBS_STAT_END_CODE]);
        }
        $this->save();
        $this->addSequenceOrder($newOrderId);
    }

    public function cancelSubscription()
    {
        $this->addData(['status' => Subscription::SUBS_STAT_CANCELLED_CODE]);
        $this->save();
    }

    public function getStatus()
    {
        $status = $this->getData('status');
        switch ($status) {
            case Subscription::SUBS_STAT_ACTIVE_CODE:
                return Subscription::SUBS_STAT_ACTIVE_TEXT;
            case Subscription::SUBS_STAT_INACTIVE_CODE:
                return Subscription::SUBS_STAT_INACTIVE_TEXT;
            case Subscription::SUBS_STAT_END_CODE:
                return Subscription::SUBS_STAT_END_TEXT;
            case Subscription::SUBS_STAT_CANCELLED_CODE:
                return Subscription::SUBS_STAT_CANCELLED_TEXT;
            default:
                return "Unknown";
        }
    }

    public function isOwn(
        $customerId
    ) {
        return $this->getData('customer_id') == $customerId;
    }

    public function canCancel()
    {
        return $this->getData('status') != Subscription::SUBS_STAT_CANCELLED_CODE;
    }

    public function addSequenceOrder(
        $orderId
    ) {
        $sequenceOrderIds = $this->getData('sequence_order_ids');
        $newOrderId = ($sequenceOrderIds == null) ? $orderId : $sequenceOrderIds . "-" . $orderId;
        $this->addData(['sequence_order_ids' => $newOrderId])->save();
    }
}
