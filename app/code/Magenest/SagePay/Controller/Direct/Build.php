<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Direct;

use Magenest\SagePay\Helper\SagepayAPI;
use Magenest\SagepayLib\Classes\Constants;
use Magenest\SagepayLib\Classes\SagepayApiException;
use Magenest\SagepayLib\Classes\SagepaySettings;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Build extends Action
{
    protected $coreRegistry;

    protected $_formKeyValidator;

    protected $checkoutSession;

    protected $sageHelper;

    protected $sageLogger;

    protected $quoteManager;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        \Magento\Quote\Model\QuoteManagement $quoteManagement

    )
    {
        $this->quoteManager = $quoteManagement;
        $this->coreRegistry = $registry;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->sageHelper = $sageHelper;
        $this->sageLogger = $sageLogger;
    }

    public function execute()
    {
        try {
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new SagepayApiException("Invalid Form Key");
            }
            if ($this->getRequest()->isAjax()) {
                $quoteId = $this->getRequest()->getParam('quote_id');
                $cardType = $this->getRequest()->getParam('cc_type');
                $card = $this->getRequest()->getParam('card');
                $billingAddress = json_decode($this->getRequest()->getParam('billing_address'), true);
                $shippingAddress = json_decode($this->getRequest()->getParam('shipping_address'), true);
                $guestEmail = $this->getRequest()->getParam('guest_email');
                $quote = $this->checkoutSession->getQuote();
                $pay = $quote->getPayment();
                $quoteDetails = $this->sageHelper->getPaymentDetail($quote, $billingAddress, $shippingAddress,
                    $guestEmail);
                $config = [
                    'currency' => strtoupper($quote->getBaseCurrencyCode()),
                    'txType' => $this->sageHelper->getSageFormPaymentAction()
                ];
                $apiConfig = array_merge_recursive($this->sageHelper->getSageApiConfigArray(), $config);
                $sageConfig = SagepaySettings::getInstance($apiConfig, false);
//            $sageConfig = \SagepaySettings::getInstance();
                $sageApi = new SagepayAPI($sageConfig, 'direct');
                $quoteDetails['CardType'] = $cardType;
                $quoteDetails['cardType'] = $card['cardType'];
                $quoteDetails['cardNumber'] = $card['cardNumber'];
                $quoteDetails['cardHolder'] = $card['cardHolder'];
                $quoteDetails['expiryDate'] = $card['expiryDate'];
                $quoteDetails['cv2'] = $card['cv2'];

                $api = $sageApi->buildApi($quote, $quoteDetails);

                if ($api) {
                    $request = $api->createRequest();
                    $vendorTxCode = $api->getData()['VendorTxCode'];
                    $quote->setPaymentMethod('magenest_sagepay_direct');
                    $quote->getPayment()->setAdditionalInformation("vendor_tx_code", $vendorTxCode);
                    if ($request['Status'] == "3DAUTH") {
                        $quote->getPayment()->setAdditionalInformation("sage_3ds_active", "true");
                        $quote->getPayment()->setAdditionalInformation("sage_3ds_url", $request['ACSURL']);
                        $quote->getPayment()->setAdditionalInformation("sage_3ds_pareq", $request['PAReq']);
                        $quote->getPayment()->setAdditionalInformation("sage_trans_id_secure", $request['MD']);
                    } elseif ($request['Status'] !="3DAUTH") {
                        $quote->getPayment()->setAdditionalInformation("sage_3ds_active", "false");

                    }
//                    $quote->getPayment()->importData(['method' => 'magenest_sagepay_direct']);
                    $quote->save();
                    $queryString = htmlspecialchars(rawurldecode(utf8_encode($api->getQueryData())));
                    $this->sageLogger->debug($queryString);
                    $result->setData([
                        'success' => true,
                        'request' => $request,
                        'purchaseUrl' => $sageConfig->getPurchaseUrl(Constants::SAGEPAY_DIRECT, $sageConfig->getEnv()),
                        'string' => $queryString
                    ]);
                } else {
                    $result->setData([
                        'error' => true,
                        'message' => __("Payment Request Error")
                    ]);
                }
            } else {
                $result->setData([
                    'error' => true,
                    'message' => __("Invalid request")
                ]);
            }
        } catch (SagepayApiException $e) {
            $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'error' => true,
                'message' => __("Payment error")
            ]);
        } finally {
            return $result;
        }
    }
}
