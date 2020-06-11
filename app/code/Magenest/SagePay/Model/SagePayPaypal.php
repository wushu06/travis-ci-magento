<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Model;

use Magenest\SagePay\Helper\SagepayAPI;
use Magenest\SagePay\Model\Source\PaymentAction;
use Magenest\SagepayLib\Classes\SagepayUtil;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Model\InfoInterface;
use Magenest\SagepayLib\Classes\SagepaySettings;
use Magenest\SagepayLib\Classes\Constants;
use Magenest\SagepayLib\Classes\SagepayCommon;

class SagePayPaypal extends \Magento\Payment\Model\Method\AbstractMethod
{

    const CODE = 'magenest_sagepay_paypal';

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canOrder = false;
    protected $_canUseInternal = false;
    protected $formKey;
    protected $sageHelper;
    protected $checkoutSession;
    protected $sageDirectModel;
    protected $url;
    protected $_isInitializeNeeded = true;
    protected $sageLogger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magento\Framework\UrlInterface $url,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->url = $url;
        $this->checkoutSession = $session;
        $this->sageHelper = $sageHelper;
        $this->formKey = $formKey;
        $this->sageLogger = $sageLogger;
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource, $resourceCollection, $data);
    }

    public function initialize($paymentAction, $stateObject)
    {
        $this->sageLogger->debug("Begin SagePay Paypal");
        $payment = $this->getInfoInstance();
        $this->sageLogger->debug("orderId: " . $payment->getOrder()->getIncrementId());
        $quote = $this->checkoutSession->getQuote();

        if (!$quote) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Invalid Quote")
            );
        }

        if ($paymentAction != PaymentAction::AUTHORIZE && $paymentAction != PaymentAction::AUTHORIZE_AND_CAPTURE) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Invaild payment action")
            );
        }

        $response = $this->getApiResponse($quote, $payment);
        if ($this->isPaymentSuccess($response)) {
            $this->savePaymentInformation($payment, $response, $paymentAction);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Payment Error: " . $response['StatusDetail'])
            );
        }
        return parent::initialize($paymentAction, $stateObject);
    }


    public function isPaymentSuccess($response)
    {
        $status = isset($response['Status']) ? $response['Status'] : '';
        return $status == Constants::SAGEPAY_REMOTE_STATUS_PAYPAL_REDIRECT;
    }

    public function savePaymentInformation(InfoInterface $payment, $response, $paymentAction)
    {
        if ($paymentAction == PaymentAction::AUTHORIZE) {
            $payment->setAdditionalInformation('is_authorize', true);
        }
        $transactionId = isset($response['VPSTxId']) ? $response['VPSTxId'] : '';
        $transactionId = str_replace(["{", "}"], "", $transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setAdditionalInformation('transaction_id', $transactionId);
        $payment->setAdditionalInformation('paypal_redirect_url', isset($response['PayPalRedirectURL']) ? $response['PayPalRedirectURL'] : '');
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $additionalData = $data->getData('additional_data');
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation('customerBrowserInfo', isset($additionalData['browserInfo']) ? $additionalData['browserInfo'] : '');
        $payment->setAdditionalInformation('billingAddress', $additionalData['billing_address']);
        $payment->setAdditionalInformation('shippingAddress', $additionalData['shipping_address']);
        return $this;
    }

    public function getApiResponse($quote, $payment)
    {
        $sageApi = $this->createSageApi($quote);
//        $sageApi->setPaypalCallbackUrl($this->url->getUrl('sagepay/paypal/postBack'));
        $quoteDetails = $this->buildSageQuoteDetails($quote, $payment);
        $browserInfo = $this->getBrowserInfo($payment); // use in 3dv2
        $quoteDetails = array_merge($quoteDetails, $browserInfo);
        $api = $sageApi->buildApi($quote, $quoteDetails);
        $api->setIntegrationMethod(Constants::SAGEPAY_PAYPAL);
        if ($api) {
            $response = $api->createRequest();
            $requestForLog = SagepayUtil::arrayToQueryStringRemovingSensitiveData($api->getData(), array_keys($api->getData()));
            $this->sageHelper->debug("Paypal Request");
            $this->sageLogger->debug($requestForLog);
            $this->sageHelper->debug("Paypal Response");
            $this->sageHelper->debug($response);
            return $response;
        }
        return false;
    }

    public function buildSageQuoteDetails($quote, $payment)
    {
        $billingAddress = $quote->getBillingAddress();
        $guessEmail = $billingAddress->getEmail();
        $billingAddress = json_decode($payment->getAdditionalInformation('billingAddress'), true);
        $shippingAddress = json_decode($payment->getAdditionalInformation('shippingAddress'), true);
        $quoteDetails = $this->sageHelper->getPaymentDetail($quote, $billingAddress, $shippingAddress, $guessEmail);
        $quoteDetails['CardType'] = 'PAYPAL';
        return $quoteDetails;
    }

    public function createSageApi($quote)
    {
        $config = [
            'currency' => strtoupper($quote->getBaseCurrencyCode()),
            'txType' => $this->sageHelper->getSageDirectPaypalAction(),
            'billingAgreement' => intval($this->sageHelper->getPaypalBillingAgreement()),
            'ThreeDSNotificationURL' => 'sagepay/paypal/postback?form_key=' . $this->formKey->getFormKey()
        ];
        $apiConfig = array_merge_recursive($this->sageHelper->getSageApiConfigArray(), $config);
        $sageConfig = SagepaySettings::getInstance($apiConfig, false);
        $sageApi = new SagepayAPI($sageConfig, 'direct');
        return $sageApi;
    }

    public function getBrowserInfo(\Magento\Payment\Model\InfoInterface $payment)
    {
        $customerBrowserInfo = json_decode($payment->getAdditionalInformation('customerBrowserInfo'), true);
        $browserInfo = [
            'BrowserJavaEnabled' => $customerBrowserInfo['BrowserJavaEnabled'] ? 1 : 0,
            'BrowserColorDepth' => $customerBrowserInfo['BrowserColorDepth'],
            'BrowserScreenHeight' => $customerBrowserInfo['BrowserScreenHeight'],
            'BrowserScreenWidth' => $customerBrowserInfo['BrowserScreenWidth'],
            'BrowserTZ' => $customerBrowserInfo['BrowserTZ']
        ];
        return $browserInfo;
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return parent::authorize($payment, $amount);
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return parent::capture($payment, $amount);
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return parent::refund($payment, $amount);
    }

}