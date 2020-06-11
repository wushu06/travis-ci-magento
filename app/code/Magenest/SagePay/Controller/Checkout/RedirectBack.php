<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magenest\SagePay\Helper\Data;
use Magenest\SagePay\Model\TransactionFactory;

class RedirectBack extends Action
{
    protected $_helper;

    protected $_transFactory;

    protected $sageHelper;

    protected $sageLogger;

    protected $checkoutSession;

    protected $orderSender;

    protected $orderFactory;

    protected $orderObject;

    protected $quoteFactory;

    protected $_serialize;

    public function __construct(
        Context $context,
        Data $data,
        TransactionFactory $transactionFactory,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->_serialize = $serialize;
        $this->_helper = $data;
        $this->_transFactory = $transactionFactory;
        $this->sageHelper = $sageHelper;
        $this->sageLogger = $sageLogger;
        $this->checkoutSession = $checkoutSession;
        $this->orderSender = $orderSender;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $params = $this->getRequest()->getParams();
            $pares = $this->getRequest()->getParam('PaRes');
            $paMD = $this->getRequest()->getParam('MD');
            $order = $this->getOrder();
            $payment = $order->getPayment();
            //handle for duplicate response
            if ($payment->getAdditionalInformation('is_sagepay_processing_success')) {
                return $this->redirectSuccessPage($order);
            }
            $this->_debug($params);
            $url = $this->_helper->getPiEndpointUrl();
            $resultSubmit3d = $this->_helper->submit3D($pares, $paMD);
            if (isset($resultSubmit3d->status)) {
                //3ds check ok
                $transUrl = $url . '/transactions/' . $paMD;
                $response = $this->_helper->sendRequest($transUrl, null);
                $quoteId = $order->getQuoteId();
                $quote = $this->quoteFactory->create()->load($quoteId);
                $this->_eventManager->dispatch("magenest_sagepay_save_transaction", ['transaction_data' => $this->sageHelper->getPiResponseData($response, $quote, "Payment")]);
                $this->_debug($response);
                if (isset($response['statusCode']) && ($response['statusCode'] == 0000)) {
                    $ccLast4 = $response['paymentMethod']['card']['lastFourDigits'];
                    $expiryDate = $response['paymentMethod']['card']['expiryDate'];
                    $expM = substr($expiryDate, 0, 2);
                    $expY = substr($expiryDate, -2);
                    $ccType = $response['paymentMethod']['card']['cardType'];
                    $payment->setCcLast4($ccLast4);
                    $payment->setCcType($ccType);
                    $payment->setCcExpMonth($expM);
                    $payment->setCcExpYear($expY);

                    $payment->setAdditionalInformation("sagepay_response", $this->_serialize->serialize($response));
                    $payAction = $payment->getAdditionalInformation("sage_payment_action");
                    $totalDue = $order->getTotalDue();
                    $baseTotalDue = $order->getBaseTotalDue();
                    if ($payAction == 'authorize') {
                        $payment->authorize(true, $baseTotalDue);
                        // base amount will be set inside
                        $payment->setAmountAuthorized($totalDue);
                    }
                    if ($payAction == 'authorize_capture') {
                        $payment->setAmountAuthorized($totalDue);
                        $payment->setBaseAmountAuthorized($baseTotalDue);
                        $payment->capture(null);
                    }
                    $payment->setAdditionalInformation("is_sagepay_processing_success", true);
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $order->save();
                    return $this->_redirect('checkout/onepage/success');
                }else{
                    $errorMsg = isset($response['statusDetail']) ? $response['statusDetail'] : "3d secure authenticate fail";
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($errorMsg)
                    );
                }
            } else {
                //3ds fail
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('3d secure authenticate fail')
                );
            }
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->sageLogger->critical($e->getMessage());
            sleep(3);
            $this->orderObject = null;
            $order = $this->getOrder();
            $payment = $order->getPayment();
            //duplicate response.
            if ($payment->getAdditionalInformation('is_sagepay_processing_success')) {
                return $this->redirectSuccessPage($order);
            } else
            {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('checkout/cart');
            }
        }
        catch (\Exception $e) {
            $this->sageLogger->critical($e->getMessage());
            $this->_redirect('checkout/cart');
        }
        return $this->_redirect('checkout/cart');
    }

    private function redirectSuccessPage($order){
        $quoteId = $order->getQuoteId();
        $this->checkoutSession->setLastQuoteId($quoteId);
        $this->checkoutSession->setLastSuccessQuoteId($quoteId);
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
        $increment_id = $order->getRealOrderId();
        $this->messageManager->addSuccessMessage("Your order (ID: $increment_id) was successful!");
        return $this->_redirect("checkout/onepage/success");
    }

    private function cancelOrder()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $order->cancel();
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
        $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
        $order->save();
        $payment = $order->getPayment();
        $payment
            ->setShouldCloseParentTransaction(1)
            ->setIsTransactionClosed(1);
    }

    private function getOrder(){
        if(!$this->orderObject) {
            $transactionId = $this->getRequest()->getParam('MD');
            $transactionModel = $this->_transFactory->create()->load($transactionId, "transaction_id");
            $orderId = $transactionModel->getData('order_id');
            if ($orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                if ($order->getId()) {
                    $this->orderObject = $order;
                }
            }
        }
        if($this->orderObject){
            return $this->orderObject;
        }else {
            return $this->checkoutSession->getLastRealOrder();
        }
    }

    /**
     * @param array|string $debugData
     */
    private function _debug($debugData)
    {
        $this->sageLogger->debug(var_export($debugData, true));
    }
}
