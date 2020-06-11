<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Form;

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

    protected $_serialize;

    protected $cartRepository;

    protected $_transactionCollectionFactory;

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
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magenest\SagePay\Helper\Data $dataHelper,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magenest\SagePay\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
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
        $this->cartRepository = $cartRepository;
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
    }

    public function execute()
    {
        try {
            $crypt = $this->getRequest()->getParam('crypt');
            $response = $this->sageHelper->decryptResp($crypt);
            $decryptResponse = isset($response['decrypt']) ? $response['decrypt'] : [];

            $status = isset($decryptResponse['Status']) ? $decryptResponse['Status'] : '';
            $statusDetail = isset($decryptResponse['StatusDetail']) ? $decryptResponse['StatusDetail'] : '';
            $vendorTxCode = isset($decryptResponse['VendorTxCode']) ? $decryptResponse['VendorTxCode'] : '';
            $cardSecure = isset($decryptResponse['3DSecureStatus']) ? $decryptResponse['3DSecureStatus'] : '';
            $transactionId = isset($decryptResponse['VPSTxId']) ? $decryptResponse['VPSTxId'] : "";
            $transactionId2 = str_replace(["{", "}"], "", $transactionId);
            $checkOrder = $this->_transactionCollectionFactory->create()->addFieldToFilter('transaction_id', $transactionId2)->getData();
            if (isset($checkOrder) && count($checkOrder) > 0) {
                return $this->_redirect("checkout/onepage/success");
            } else {
                $transaction = $this->_transactionCollectionFactory->create()->addFieldToFilter('vendor_tx_code', $vendorTxCode)->getLastItem();
                $quote = $this->quoteFactory->create()->load($transaction->getQuoteId());

                $this->_eventManager->dispatch("magenest_sagepay_save_transaction", ['transaction_data' => $this->sageHelper->getResponseData($decryptResponse, $quote, "Form")]);
                $quote->getPayment()->setAdditionalInformation("sagepay_transaction_id", $transactionId2);
                if (!$this->customerSession->isLoggedIn()) {
                    $quote->setCheckoutMethod(\Magento\Quote\Model\QuoteManagement::METHOD_GUEST);
                }
                $this->cartRepository->save($quote);
                $this->sageLogger->debug(var_export($response, true));
                //set checkout session load inactive quote
                $payment = $quote->getPayment();
                if ($status == "OK") {
                    //create order
                    $quote->getPayment()->setMethod('magenest_sagepay_form');
                    $data = [
                        'transactionType' => 'Form',
                        'status' => $status,
                        'card_secure' => $cardSecure,
                        'vendorTxCode' => $vendorTxCode,
                        'statusDetail' => $statusDetail
                    ];
                    $payment->setAdditionalInformation("transaction_details", $this->_serialize->serialize($decryptResponse));
                    $payment->setAdditionalInformation("sagepay_response", $this->_serialize->serialize($data));
                    $quote->setIsActive(true);
                    $this->cartRepository->save($quote);
                    $orderId = $this->quoteManagement->placeOrder($quote->getId(), $payment);
                    $order = $this->orderFactory->create()->load($orderId);
                    $payment = $order->getPayment();
                    $order->addStatusHistoryComment($statusDetail);
                    $payment->setIsTransactionClosed(0);
                    $payment->setShouldCloseParentTransaction(0);
                    $order->save();
                    $increment_id = $order->getRealOrderId();
                    $this->messageManager->addSuccessMessage("Your order (ID: $increment_id) was successful!");
                    $this->messageManager->addSuccessMessage($status . " - " . $statusDetail);
                    $this->_checkoutSession->setTransctionId($transactionId2);
                    return $this->_redirect("checkout/onepage/success");
                } else {
                    throw new SagepayApiException($status . " - " . $statusDetail);
                }
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
