<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Paypal;

use Magenest\SagePay\Helper\SagepayAPI;
use Magenest\SagepayLib\Classes\Constants;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magenest\SagepayLib\Classes\SagepayCommon;

class PostBack extends Action{

    protected $orderRepository;

    protected $transactionFactory;

    protected $checkoutSession;

    protected $sagePayConfig;

    private $orderObject;

    protected $sageHelper;

    protected $orderSender;

    public function __construct(
        Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory,
        \Magenest\SagePay\Model\SagePayDirect $sagePayDirect,
        \Magento\Checkout\Model\Session $session,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    )
    {
        $this->transactionFactory = $transactionFactory;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $session;
        $this->sagePayConfig = $sagePayDirect->getSagePayConfig();
        $this->sageHelper = $sageHelper;
        $this->orderSender = $orderSender;
        parent::__construct($context);
    }

    public function execute()
    {
        if($response = $this->processPayment()){
            $quote = $this->checkoutSession->getQuote();
            $transactionData = $this->sageHelper->getResponseData($response, $quote, "Paypal");
            $transactionData['order_id'] = $this->checkoutSession->getLastRealOrder()->getId();
            $this->_eventManager->dispatch("magenest_sagepay_save_transaction", ['transaction_data' => $transactionData]);
            if (($response['Status'] == Constants::SAGEPAY_REMOTE_STATUS_OK) || ($response['Status'] == Constants::SAGEPAY_REMOTE_STATUS_REGISTERED)) {
                $this->completeOrder();
                return $this->_redirect('checkout/onepage/success');
            }else{
                //$this->cancelOrder();
                $statusDetail = isset($response['StatusDetail']) ? $response['StatusDetail']: __("Payment Error: Unknown Error");
                $this->messageManager->addError($statusDetail);
                return $this->_redirect('checkout/cart');
            }
        }else{
            //$this->cancelOrder();
            $errorMessage = filter_input(INPUT_POST, 'StatusDetail');
            $this->messageManager->addError(__("Payment Error: " . isset($errorMessage) ? $errorMessage : 'Unknown error!' ));
            return $this->_redirect('checkout/cart');
        }
    }

    /**
     * @return Order
     */
    public function getOrder(){
        return $this->checkoutSession->getLastRealOrder();
    }

    public function completeOrder()
    {
        $order = $this->getOrder();
        $payment = $order->getPayment();

        if ($payment->getAdditionalInformation('is_authorize')) {
            $payment->setIsTransactionClosed(false);
            $payment->setShouldCloseParentTransaction(false);
            $amount = $order->getBaseGrandTotal();
            if ($order && $amount) {
                $payment->authorize(true, $amount);
            }
        } else {
            $order = $payment->getOrder();
            $amount = $order->getBaseGrandTotal();
            if ($order && $amount && $payment->canCapture()) {
                $payment->capture();
            }
        }
        $this->orderSender->send($order);
        $this->orderRepository->save($order);
    }

    /**
     * @return bool
     */
    public function processPayment(){
        if($this->getRequest()->getParam('vtx') && $this->getRequest()->getParam('Status') == Constants::SAGEPAY_REMOTE_STATUS_PAYPAL_OK){
            $transactionDetail = $this->getTransactionDetail();
            return $transactionDetail;
        }
        return false;
    }

    public function getTransactionDetail(){
        $order = $this->getOrder();
        $data = array(
            'VPSProtocol' => $this->sagePayConfig->getProtocolVersion(),
            'TxType' => Constants::SAGEPAY_TXN_COMPLETE,
            'VPSTxId' => $this->getRequest()->getParam('VPSTxId'),
            'Amount' => number_format($order->getBaseGrandTotal(), 2, '.',''),
            'Accept' => $this->getRequest()->getParam('Status') == Constants::SAGEPAY_REMOTE_STATUS_PAYPAL_OK ? 'YES' : 'NO'
        );

        $result = SagepayCommon::requestPost($this->sagePayConfig->getPurchaseUrl('paypal'), $data);
        $paymentDetails = array_merge(filter_input_array(INPUT_POST), $result);
        return $paymentDetails;
    }

    public function cancelOrder(){
        $order = $this->getOrder();
        $order->cancel();
        $this->orderRepository->save($order);
        $this->checkoutSession->restoreQuote();
    }
}