<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Server;

use Magenest\SagepayLib\Classes\Constants;
use Magenest\SagepayLib\Classes\SagepayApiException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class Success extends Action
{
    protected $_checkoutSession;

    protected $sageHelper;

    protected $customerSession;

    protected $quoteManagement;

    protected $orderFactory;

    protected $quoteFactory;

    protected $quote;

    protected $sageLogger;

    protected $orderSender;

    protected $dataHelper;

    protected $transaction;

    protected $_serialize;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magenest\SagePay\Helper\Data $dataHelper,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magenest\SagePay\Model\TransactionFactory $transaction
    )
    {
        parent::__construct($context);
        $this->_serialize = $serialize;
        $this->_checkoutSession = $checkoutSession;
        $this->sageHelper = $sageHelper;
        $this->customerSession = $customerSession;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        $this->sageLogger = $sageLogger;
        $this->orderSender = $orderSender;
        $this->dataHelper = $dataHelper;
        $this->transaction = $transaction;
    }

    public function execute()
    {
        try {
            $data = $this->_checkoutSession->getData('magenest_sagepay_server');
            $status = isset($data['Status']) ? $data['Status'] : "Complete";
            $statusDetail = isset($data['StatusDetail']) ? $data['StatusDetail'] : __("Payment Complete");
            $vendorTxCode = isset($data['VendorTxCode']) ? $data['VendorTxCode'] : "";
            $cardSecure = isset($data['3DSecureStatus']) ? $data['3DSecureStatus'] : "";
            $transactionId = isset($data['VPSTxId']) ? $data['VPSTxId'] : "";
            $transactionId = str_replace(["{", "}"], "", $transactionId);
            $transactionModel = $this->transaction->create()->load($transactionId, "transaction_id");
            $quote = $this->_checkoutSession->getQuote();
            if ($transactionModel->getId()) {
                $transactionModel->setData("quote_id", $quote->getId());
                $transactionModel->save();
            }
//            $this->quote = $this->quoteFactory->create()->load($this->getRequest()->getParam('quoteid'));
            $quote = $this->_checkoutSession->getQuote();
            $quote->getPayment()->setAdditionalInformation("sagepay_transaction_id", $transactionId);
            if (!$this->customerSession->isLoggedIn()) {
                $quote->setCheckoutMethod(\Magento\Quote\Model\QuoteManagement::METHOD_GUEST);
            }
            $quote->save();
            $payment = $quote->getPayment();
            if (($status == "OK") && ($vendorTxCode == $payment->getAdditionalInformation('vendor_tx_code'))) {
                //create order
                $quote->getPayment()->setMethod('magenest_sagepay_server');

                $sagedata = [
                    'transactionType' => 'Server',
                    'status' => $status,
                    'card_secure' => $cardSecure,
                    'vendorTxCode' => $vendorTxCode,
                    'statusDetail' => $statusDetail
                ];
                $payment->setAdditionalInformation("sagepay_response", $this->_serialize->serialize($sagedata));
                $quote->save();

                $orderId = $this->quoteManagement->placeOrder($quote->getId(), $payment);
                $order = $this->orderFactory->create()->load($orderId);
                $payment = $order->getPayment();
                $order->addStatusHistoryComment($statusDetail);
                $payment->setIsTransactionClosed(0);
                $payment->setShouldCloseParentTransaction(0);
                $payment->setAdditionalInformation('vendorTxCode', $vendorTxCode);
                $payment->setADditionalInformation('profile', $data['Profile']);
                $payment->setADditionalInformation('securityKey', $data['SecurityKey']);
                $payment->setADditionalInformation('referrerId', $data['ReferrerID']);
                $order->save();
                $items = $order->getAllItems();
                $payment->setAdditionalInformation("sagepay_response", $this->_serialize->serialize($data));
                $increment_id = $order->getRealOrderId();
                $this->messageManager->addSuccessMessage("Your order (ID: $increment_id) was successful!");
                $this->messageManager->addSuccessMessage($status . " - " . $statusDetail);
                $this->_checkoutSession->unsData('magenest_sagepay_server');
                if ($data['Profile'] == Constants::SAGEPAY_SERVER_PROFILE_NORMAL) {
                    return $this->_redirect("checkout/onepage/success");
                } else {
                    return $this->_redirect("sagepay/server/lowprofilesuccess");
                }
            } else {
                throw new SagepayApiException($status . " - " . $statusDetail);
            }
        } catch (SagepayApiException $e) {
            $this->dataHelper->debugException($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->sageLogger->critical($e->getMessage());
            return $this->_redirect("checkout/cart");
        } catch (\Exception $e) {
            $this->dataHelper->debugException($e);
            $this->messageManager->addErrorMessage(__("Payment exception"));
            $this->sageLogger->critical($e->getMessage());
            return $this->_redirect("checkout/cart");
        }
    }
}