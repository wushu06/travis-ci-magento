<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magenest\SagepayLib\Classes\SagepayApiException;
use Magenest\SagepayLib\Classes\SagepayUtil;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;

class SageHelper extends AbstractHelper
{

    const SECURE_STATUS_APPLY = "Authenticated";
    const SECURE_STATUS_NOT_APPLY = "NotAuthenticated";
    const SECURE_STATUS_NOT_ENROLL = "CardNotEnrolled";
    const SECURE_STATUS_ISSUER_NOT_ENROLL = "IssuerNotEnrolled";

    const SAGE_TRANSACTION_ID = "transaction_id";

    const SAGE_PAY_TYPE_AUTHORIZE = "Deferred";
    const SAGE_PAY_TYPE_CAPTURE = "Payment";
    const SAGE_PAY_TYPE_REPEAT = "Repeat";
    const SAGE_PAY_TYPE_REFUND = "Refund";
    const SAGE_PAY_TYPE_RELEASE = "Release";
    const SAGE_PAY_TYPE_VOID = "Void";

    const SAGE_PAY_TYPE_INSTRUCTION_VOID = "void";
    const SAGE_PAY_TYPE_INSTRUCTION_ABORT = "abort";
    const SAGE_PAY_TYPE_INSTRUCTION_RELEASE = "release";

    const SAGEPAY_SHARED_REFUND_TRANSACTION_TEST = 'https://test.sagepay.com/gateway/service/refund.vsp';
    const SAGEPAY_SHARED_REFUND_TRANSACTION_LIVE = 'https://live.sagepay.com/gateway/service/refund.vsp';
    const SAGEPAY_SHARED_RELEASE_TRANSACTION_TEST ='https://test.sagepay.com/gateway/service/release.vsp';
    const SAGEPAY_SHARED_RELEASE_TRANSACTION_LIVE ='https://live.sagepay.com/gateway/service/release.vsp';
    const SAGEPAY_SHARED_VOID_TRANSACTION_TEST = 'https://test.sagepay.com/gateway/service/void.vsp';
    const SAGEPAY_SHARED_VOID_TRANSACTION_LIVE = 'https://live.sagepay.com/gateway/service/void.vsp';
    const SAGEPAY_SHARED_ABORT_TRANSACTION_TEST = 'https://test.sagepay.com/gateway/service/abort.vsp';
    const SAGEPAY_SHARED_ABORT_TRANSACTION_LIVE = 'https://live.sagepay.com/gateway/service/abort.vsp';

    const DEFERRED_AWAITING_RELEASE = 14;
    const SUCCESSFULLY_AUTHORISED   = 16;

    protected $orderFactory;
    protected $_encryptor;
    protected $_curlFactory;
    protected $storeManager;
    protected $cardFactory;
    protected $customerSession;
    protected $encryptor;
    protected $sageLogger;
    protected $sessionQuote;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magenest\SagePay\Model\CardFactory $cardFactory,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        Context $context
    )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->_encryptor = $encryptor;
        $this->_curlFactory = $curlFactory;
        $this->storeManager = $storeManager;
        $this->cardFactory = $cardFactory;
        $this->customerSession = $customerSession;
        $this->sageLogger = $sageLogger;
        $this->sessionQuote = $sessionQuote;
    }

    public function getPiEndpointUrl()
    {
        if ($this->getIsSandbox()) {
            return 'https://pi-test.sagepay.com/api/v1';
        } else {
            return 'https://pi-live.sagepay.com/api/v1';
        }
    }

    public function getIsSandbox()
    {
        return $this->getConfigValue('test');
    }

    public function getConfigValue($value, $encrypted = false, $order = null)
    {
        $configValue = $this->scopeConfig->getValue(
            'payment/magenest_sagepay/' . $value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if($order) {
            $configValue = $this->scopeConfig->getValue(
                'payment/magenest_sagepay/' . $value,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $order->getStore()->getCode()
            );
        }
        if ($encrypted) {
            return $this->_encryptor->decrypt($configValue);
        } else {
            return $configValue;
        }
    }

    public function getCanSave()
    {
        return $this->getConfigValue('can_save_card');
    }

    public function debug($debugData)
    {
        $this->sageLogger->debug(var_export($debugData, true));
    }

    public function isDebugMode()
    {
        return $this->getConfigValue('debug');
    }

    public function buildInstructionUrl($transId)
    {
        return $this->getPiEndpointUrl() . '/transactions/' . $transId . "/instructions";
    }

    public function getEndpointUrl()
    {
        if ($this->getIsSandbox()) {
            return 'https://test.sagepay.com/api/v1';
        } else {
            return 'https://live.sagepay.com/api/v1';
        }
    }

    public function isGiftAid()
    {
        return $this->getConfigValue('gift_aid');
    }

    public function getInstructions()
    {
        return preg_replace('/\s+|\n+|\r/', ' ', $this->getConfigValue('instructions'));
    }

    public function get3DStatusAllow()
    {
        $data = $this->scopeConfig->getValue(
            'payment/magenest_sagepay/additional_config/apply_3d_allow',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$data) {
            $data = "Authenticated,NotChecked,NotAuthenticated,Error,CardNotEnrolled,IssuerNotEnrolled,MalformedOrInvalid,AttemptOnly,Incomplete";
        }
        return $data;
    }

    public function useDropIn()
    {
        return $this->getConfigValue('use_dropin');
    }

    public function getDropInMode()
    {
        return $this->getConfigValue('dropin_mode');
    }

    /**
     * @param Order $order
     * @param $transId
     * @param $amount
     * @return string
     */
    public function buildInstructionQuery($order, $instructionType, $amount = 0)
    {
        $currencyCode = $order->getBaseCurrencyCode();
        $multiply = 100;
        if ($this->isZeroDecimal($currencyCode)) {
            $multiply = 1;
        }
        $amount = round($amount * $multiply);
        $query = '{' .
            '"instructionType": "' . $instructionType . '"';

        if ($instructionType == self::SAGE_PAY_TYPE_INSTRUCTION_RELEASE) {
            $query .= ',"amount": ' . $amount;
        }
        $query .= '}';

        return $query;
    }

    /**
     * @param Order $order
     * @param $sessionKey
     * @param $cardId
     * @param $type
     * @param null|bool $save
     * @param null|bool $reusable
     * @return string
     */
    public function buildPaymentQuery($order, $sessionKey, $cardId, $type, $save = null, $reusable = null)
    {
        $ignoreAddressCheck = $this->getConfigValue('ignore_address_check');
        $testMode = $this->getIsSandbox();
        if ($ignoreAddressCheck && $testMode) {
            $address1 = "88";
            $address2 = "88";
            $postCode = "412";
        } else {
            $address1 = $order->getBillingAddress()->getStreetLine(1);
            $address2 = $order->getBillingAddress()->getStreetLine(2);
            $postCode = $order->getBillingAddress()->getPostcode();
        }
        $save = ($save == true) ? 'true' : 'false';
        $reusable = ($reusable == true) ? 'true' : 'false';
        $amount = $order->getBaseGrandTotal();
        $currencyCode = $order->getBaseCurrencyCode();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();
        $giftAid = ($payment->getAdditionalInformation('gift_aid')) ? "true" : "false";
        $multiply = 100;
        if ($this->isZeroDecimal($currencyCode)) {
            $multiply = 1;
        }
        $amount = round($amount * $multiply);
        $payload = '{' .
            '"transactionType": "' . $type . '",' .
            '"paymentMethod": {' .
            '    "card": {';
        if (1) {
            $payload .=
                '        "merchantSessionKey": "' . $sessionKey . '",';
        }
        $payload .=
            '        "cardIdentifier": "' . $cardId . '",' .
            '        "save": "' . $save . '",' .
            '        "reusable": "' . $reusable . '"' .
            '    }' .
            '},' .
            '"vendorTxCode": "' . $this->generateVendorTxCode($order->getIncrementId(), $type) . '",' .
            '"amount": ' . $amount . ',' .
            '"currency": "' . $currencyCode . '",' .
            '"description": "' . $this->getPaymentDescription($order) . '",' .
            '"apply3DSecure": "' . $this->getIsApply3DSecure() . '",' .
            '"applyAvsCvcCheck": "' . $this->getIsApplyCvcCheck() . '",' .
            '"customerFirstName": "' . $order->getBillingAddress()->getFirstname() . '",' .
            '"customerLastName": "' . $order->getBillingAddress()->getLastname() . '",' .
            '"customerEmail": "' . $order->getBillingAddress()->getEmail() . '",' .
            '"customerPhone": "' . $order->getBillingAddress()->getTelephone() . '",';
        $payload .=
            '"billingAddress": {' .
            '    "address1": "' . $address1 . '",' .
            '    "address2": "' . $address2 . '",';
        $payload .= '    "postalCode": "' . $postCode . '",';
        if ($order->getBillingAddress()->getCountryId() == 'US') {
            $payload .= '    "state": "' . $order->getBillingAddress()->getRegionCode() . '",';
        } else {
        }

        $payload .= '    "city": "' . $order->getBillingAddress()->getCity() . '",' .
            '    "country": "' . $order->getBillingAddress()->getCountryId() . '"';
        $payload .= '},';
        $shipping = $order->getShippingAddress();
        if (!!$shipping) {
            $payload .=
                '"shippingDetails": {' .
                '    "recipientFirstName": "' . $shipping->getFirstname() . '",' .
                '    "recipientLastName": "' . $shipping->getLastname() . '",' .
                '    "shippingAddress1": "' . $shipping->getStreetLine(1) . '",' .
                '    "shippingAddress2": "' . $shipping->getStreetLine(2) . '",';
            $payload .= '    "shippingPostalCode": "' . $shipping->getPostcode() . '",';
            if ($shipping->getCountryId() == 'US') {
                $payload .= '    "shippingState": "' . $shipping->getRegionCode() . '",';
            } else {
            }

            $payload .= '    "shippingCity": "' . $shipping->getCity() . '",' .
                '    "shippingCountry": "' . $shipping->getCountryId() . '"';
            $payload .= '},';
        }
        $payload .= '"giftAid": "' . $giftAid . '",';
        $payload .= '"entryMethod": "Ecommerce",';
        $payload .= '"referrerId": "1BC70868-12A8-1383-A2FB-D7A0205DE97B"';
        $payload .= '}';

        return $payload;
    }

    public function getVendorCode()
    {
        return $this->getConfigValue('vendor_code');
    }

    public function getIsApply3DSecure($disable3DSecure = false)
    {
        $config3Dsecure = $this->scopeConfig->getValue('payment/magenest_sagepay/apply_3d_secure');
        if ($disable3DSecure) {
            return "Disable";
        } else {
            return $config3Dsecure;
        }
    }

    public function getIsApply3DSecureV2($disable3DSecureV2 = false)
    {
        $config3DsecureV2 = $this->scopeConfig->getValue('payment/magenest_sagepay/enable_3ds2');
        return $config3DsecureV2;
    }

    public function getIsApplyCvcCheck()
    {
        return $this->scopeConfig->getValue('payment/magenest_sagepay/apply_cvc_check');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $transId
     * @param $amount
     * @return string
     */
    public function buildRefundQuery($order, $transId, $amount)
    {
        $currencyCode = $order->getBaseCurrencyCode();
        $multiply = 100;
        if ($this->isZeroDecimal($currencyCode)) {
            $multiply = 1;
        }
        $amount = round($amount * $multiply);
        $payload = '{' .
            '"transactionType": "Refund",' .
            '"referenceTransactionId": "' . $transId . '",' .
            '"amount": ' . $amount . ',' .
            '"vendorTxCode": "' . $this->generateVendorTxCode($order->getIncrementId(), "Refund", $order) . '",' .
            '"description": "' . $this->getRefundDescription($order) . '"';
        $payload .= '}';

        return $payload;
    }

    /**
     * @param $refTransId
     * @param Order $order
     * @return string
     */
    public function buildRepeatQuery($refTransId, $order)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();
        $giftAid = ($payment->getAdditionalInformation('gift_aid')) ? "true" : "false";
        $amount = $order->getBaseGrandTotal();
        $currencyCode = $order->getBaseCurrencyCode();
        $multiply = 100;
        if ($this->isZeroDecimal($currencyCode)) {
            $multiply = 1;
        }
        $amount = round($amount * $multiply);
        $payload = '{' .
            '"transactionType": "Repeat",' .
            '"referenceTransactionId":"' . $refTransId . '",' .
            '"vendorTxCode": "' . $this->generateVendorTxCode($order->getIncrementId(), "Repeat", $order) . '",' .
            '"amount": ' . $amount . ',' .
            '"currency": "' . $currencyCode . '",' .
            '"description": "' . $this->getRepeatDescription($order) . '",';
        $shipping = $order->getShippingAddress();
        if (!!$shipping) {
            $payload .=
                '"shippingDetails": {' .
                '    "recipientFirstName": "' . $shipping->getFirstname() . '",' .
                '    "recipientLastName": "' . $shipping->getLastname() . '",' .
                '    "shippingAddress1": "' . $shipping->getStreetLine(1) . '",' .
                '    "shippingAddress2": "' . $shipping->getStreetLine(2) . '",';
            $payload .= '    "shippingPostalCode": "' . $shipping->getPostcode() . '",';
            if ($shipping->getCountryId() == 'US') {
                $payload .= '    "shippingState": "' . $shipping->getRegionCode() . '",';
            } else {
            }
            $payload .= '    "shippingCity": "' . $shipping->getCity() . '",' .
                '    "shippingCountry": "' . $shipping->getCountryId() . '"';
            $payload .= '},';
        }
        $payload .= '"giftAid": "' . $giftAid . '",';
        $payload .= '"referrerId": "1BC70868-12A8-1383-A2FB-D7A0205DE97B"';
        $payload .= '}';

        return $payload;
    }

    public function buildLinkSecureCodeQuery($secureCode)
    {
        $payload = '{' .
            '"securityCode": "' . $secureCode . '"' .
            '}';

        return $payload;
    }

    public function sendRequest($url, $payload, $order = null)
    {
        $integrationKey = $this->getConfigValue('integration_key', true, $order);
        $integrationPass = $this->getConfigValue('integration_password', true, $order);
        $http = $this->_curlFactory->create();
        $encoded_credential = base64_encode($integrationKey . ':' . $integrationPass);
        $headers = [
            "Authorization: Basic " . $encoded_credential,
            "Cache-Control: no-cache",
            "Content-Type: application/json"
        ];

        $method = \Zend_Http_Client::POST;

        if (!$payload) {
            $method = \Zend_Http_Client::GET;
        }
        $http->write(
            $method,
            $url,
            '1.1',
//            $headers,
            $headers,
            $payload
        );
        $response = $http->read();

        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);
        $response = (array)json_decode($response, true);
        return $response;
    }

    public function sendFormRequest($url, $payload)
    {
        $integrationKey = $this->getConfigValue('integration_key', true);
        $integrationPass = $this->getConfigValue('integration_password', true);
        $http = $this->_curlFactory->create();
        $encoded_credential = base64_encode($integrationKey . ':' . $integrationPass);

        $method = \Zend_Http_Client::POST;

        if (!$payload) {
            $method = \Zend_Http_Client::GET;
        }
        $http->write(
            $method,
            $url,
            '1.1',
//            $headers,
            ['Content-Type: application/x-www-form-urlencoded'],
            $payload
        );
        $response = $http->read();

        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);

//
            $response = explode("\r\n", $response);
//
        if ($response[0] != "VPSProtocol=3.00" && $response[0] != "VPSProtocol=3" && $response[0] != "VPSProtocol=4.00" && $response[0] != "VPSProtocol=4") {
            $xml = simplexml_load_string($response[0], "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $response = json_decode($json, TRUE);
        }

        return $response;
    }

    /**
     * @param Order $order
     */
    public function getPaymentDescription($order, $isMoto = false)
    {
        $storeName = $order->getStore()->getName();
        if (!$isMoto) {
            return "Order " . $order->getIncrementId() . " at " . $storeName;
        } else {
            return "MOTO transaction. Order " . $order->getIncrementId() . " at " . $storeName;
        }
    }

    public function getRefundDescription($order)
    {
        $storeName = $order->getStore()->getName();

        return "Refund Order " . $order->getIncrementId() . " at " . $storeName;
    }

    public function getRepeatDescription($order)
    {
        $storeName = $order->getStore()->getName();

        return "Recurring Order " . $order->getIncrementId() . " at " . $storeName;
    }

    public function isZeroDecimal($currency)
    {
        return in_array(strtolower($currency), [
            'bif',
            'djf',
            'jpy',
            'krw',
            'pyg',
            'vnd',
            'xaf',
            'xpf',
            'clp',
            'gnf',
            'kmf',
            'mga',
            'rwf',
            'vuv',
            'xof'
        ]);
    }

    public function generateVendorTxCode($order_id = "", $type = "", $order = null)
    {
        $parts = [];
        $parts[] = $this->getConfigValue("vendor_name",false, $order);
        if(trim($type) != ""){
            $parts[] = strtoupper($type);
        }
        if(trim($order_id) != ""){
            $parts[] = $order_id;
        }
        $parts[] = rand(0, 1000000000);
        $vendorTxCode = implode('-', $parts);
        return substr($vendorTxCode, 0, 40);
    }

    public function activeMoto()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_sagepay/active_moto'
        );
    }

    public function buildMotoPaymentQuery($order, $sessionKey, $cardId, $type, $save = null, $reusable = null)
    {
        $ignoreAddressCheck = $this->getConfigValue('ignore_address_check');
        $testMode = $this->getIsSandbox();
        if ($ignoreAddressCheck && $testMode) {
            $address1 = "88";
            $address2 = "88";
            $postCode = "412";
        } else {
            $address1 = $order->getBillingAddress()->getStreetLine(1);
            $address2 = $order->getBillingAddress()->getStreetLine(2);
            $postCode = $order->getBillingAddress()->getPostcode();
        }
        $save = ($save == true) ? 'true' : 'false';
        $reusable = ($reusable == true) ? 'true' : 'false';
        $amount = $order->getBaseGrandTotal();
        $currencyCode = $order->getBaseCurrencyCode();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();
        $giftAid = ($payment->getAdditionalInformation('gift_aid')) ? "true" : "false";
        $multiply = 100;
        if ($this->isZeroDecimal($currencyCode)) {
            $multiply = 1;
        }
        $amount = round($amount * $multiply);
        $payload = '{' .
            '"transactionType": "' . $type . '",' .
            '"paymentMethod": {' .
            '    "card": {';
        if (1) {
            $payload .=
                '        "merchantSessionKey": "' . $sessionKey . '",';
        }
        $payload .=
            '        "cardIdentifier": "' . $cardId . '"' .
            '    }' .
            '},' .
            '"vendorTxCode": "' . $this->generateVendorTxCode($order->getIncrementId(), $type, $order) . '",' .
            '"amount": ' . $amount . ',' .
            '"currency": "' . $currencyCode . '",' .
            '"description": "' . $this->getPaymentDescription($order, true) . '",' .
            '"apply3DSecure": "' . $this->getIsApply3DSecure(true) . '",' .
            '"applyAvsCvcCheck": "' . $this->getIsApplyCvcCheck() . '",' .
            '"customerFirstName": "' . $order->getBillingAddress()->getFirstname() . '",' .
            '"customerLastName": "' . $order->getBillingAddress()->getLastname() . '",' .
            '"customerEmail": "' . $order->getBillingAddress()->getEmail() . '",' .
            '"customerPhone": "' . $order->getBillingAddress()->getTelephone() . '",';
        $payload .=
            '"billingAddress": {' .
            '    "address1": "' . $address1 . '",' .
            '    "address2": "' . $address2 . '",';
        $payload .= '    "postalCode": "' . $postCode . '",';
        if ($order->getBillingAddress()->getCountryId() == 'US') {
            $payload .= '    "state": "' . $order->getBillingAddress()->getRegionCode() . '",';
        } else {
        }

        $payload .= '    "city": "' . $order->getBillingAddress()->getCity() . '",' .
            '    "country": "' . $order->getBillingAddress()->getCountryId() . '"';
        $payload .= '},';
        $shipping = $order->getShippingAddress();
        if (!!$shipping) {
            $payload .=
                '"shippingDetails": {' .
                '    "recipientFirstName": "' . $shipping->getFirstname() . '",' .
                '    "recipientLastName": "' . $shipping->getLastname() . '",' .
                '    "shippingAddress1": "' . $shipping->getStreetLine(1) . '",' .
                '    "shippingAddress2": "' . $shipping->getStreetLine(2) . '",';
            $payload .= '    "shippingPostalCode": "' . $shipping->getPostcode() . '",';
            if ($shipping->getCountryId() == 'US') {
                $payload .= '    "shippingState": "' . $shipping->getRegionCode() . '",';
            } else {
            }

            $payload .= '    "shippingCity": "' . $shipping->getCity() . '",' .
                '    "shippingCountry": "' . $shipping->getCountryId() . '"';
            $payload .= '},';
        }
        $payload .= '"entryMethod": "TelephoneOrder",';
        $payload .= '"referrerId": "1BC70868-12A8-1383-A2FB-D7A0205DE97B"';
        $payload .= '}';

        return $payload;
    }

    /**
     * @param \stdClass $card
     */
    public function saveCard($customerId, $card)
    {
        $cardType = $card['cardType'];
        $last4 = $card['lastFourDigits'];
        $expireDate = $card['expiryDate'];
        $cardId = $card['cardIdentifier'];
        $reusable = $card['reusable'];
        if ($reusable) {
            $cardModel = $this->cardFactory->create();
            $data = [
                'customer_id' => $customerId,
                'card_id' => $cardId,
                'card_type' => $cardType,
                'last_4' => (string)$last4,
                'expire_date' => (string)$expireDate
            ];
            $cardModel->addData($data)->save();
        }
    }

    /////////////////////////////////////Sage api config section

    public function getApiEnv()
    {
        $isTest = $this->getConfigValue("test");
        if ($isTest == "1") {
            return "test";
        } else {
            return "live";
        }
    }

    public function convertApi3DsCcv($mode)
    {
        if ($mode == "UseMSPSetting") {
            return 0;
        }
        if ($mode == "Force") {
            return 1;
        }
        if ($mode == "Disable") {
            return 2;
        }
        if ($mode == "ForceIgnoringRules") {
            return 3;
        }
        return 0;
    }

    public function getEncryptedPassword()
    {
        $mode = $this->scopeConfig->getValue('payment/magenest_sagepay/test');
        if ($mode == 1) {
            return $this->getTestEncryptedPassword();
        } else{
            return $this->getLiveEncryptedPassword();
        }
    }

    public function getTestEncryptedPassword()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_sagepay_form/test_encrypted_password'
        );
    }

    public function getLiveEncryptedPassword()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_sagepay_form/live_encrypted_password'
        );
    }

    public function getSageFormPaymentAction()
    {
        $paymentAction = $this->scopeConfig->getValue(
            'payment/magenest_sagepay_form/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($paymentAction == "authorize_capture") {
            return "PAYMENT";
        }
        if ($paymentAction == "authorize") {
            return "DEFERRED";
        }
        return "PAYMENT";
    }

    public function getSageServerPaymentAction()
    {
        $paymentAction = $this->scopeConfig->getValue(
            'payment/magenest_sagepay_server/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($paymentAction == "authorize_capture") {
            return "PAYMENT";
        }
        if ($paymentAction == "authorize") {
            return "DEFERRED";
        }
        return "PAYMENT";
    }

    public function getSageDirectAction(){
        $paymentAction = $this->scopeConfig->getValue(
            'payment/magenest_sagepay_direct/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($paymentAction == "authorize_capture") {
            return "PAYMENT";
        }
        if ($paymentAction == "authorize") {
            return "DEFERRED";
        }
        return "PAYMENT";
    }

    public function getSageDirectPaypalAction(){
        $paymentAction = $this->scopeConfig->getValue(
            'payment/magenest_sagepay_paypal/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($paymentAction == "authorize_capture") {
            return "PAYMENT";
        }
        if ($paymentAction == "authorize") {
            return "DEFERRED";
        }
        return "PAYMENT";
    }
    /**
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function getPaymentDetail($quote, $billingAddress, $shippingAddress, $guestEmail)
    {
        $quoteBillingAddress = $quote->getBillingAddress();
        $quoteShippingAddress = $quote->getShippingAddress();
        $arr = [];
        if (!$this->customerSession->isLoggedIn()) {
            $arr['customerEmail'] = $guestEmail;
        } else {
            $arr['customerEmail'] = $quote->getCustomerEmail();
        }
        $arr['BillingFirstnames'] = $quoteBillingAddress->getFirstname();
        $arr['BillingSurname'] = $quoteBillingAddress->getLastname();
        $arr['BillingAddress1'] = $quoteBillingAddress->getStreetLine(1);
        $arr['BillingAddress2'] = $quoteBillingAddress->getStreetLine(2);
        $arr['BillingCity'] = $quoteBillingAddress->getCity();
        $arr['BillingPostCode'] = $quoteBillingAddress->getPostcode();
        $arr['BillingCountry'] = $quoteBillingAddress->getCountryId();
        $arr['BillingPhone'] = $quoteBillingAddress->getTelephone();
        if ($arr['BillingCountry'] == 'US') {
            $arr['BillingState'] = $quoteBillingAddress->getRegionCode();;
        }
        $arr['DeliveryFirstnames'] = $quoteShippingAddress->getFirstname();
        $arr['DeliverySurname'] = $quoteShippingAddress->getLastname();
        $arr['DeliveryAddress1'] = $quoteShippingAddress->getStreetLine(1);
        $arr['DeliveryAddress2'] = $quoteShippingAddress->getStreetLine(2);
        $arr['DeliveryCity'] = $quoteShippingAddress->getCity();
        $arr['DeliveryPostCode'] = $quoteShippingAddress->getPostcode();
        $arr['DeliveryCountry'] = $quoteShippingAddress->getCountryId();
        $arr['DeliveryPhone'] = $quoteShippingAddress->getTelephone();
        if ($arr['DeliveryCountry'] == 'US') {
            $arr['DeliveryState'] = $quoteShippingAddress->getRegionCode();
        }

        return $arr;
    }

    public function getSageApiConfigArray()
    {
        $env = $this->getApiEnv();
        $vendorName = $this->getConfigValue("vendor_name");
        $baseUrl = $this->_urlBuilder->getBaseUrl();
        $apply3Ds = $this->getIsApply3DSecure(false);
        $apply3Ds2 = $this->getIsApply3DSecureV2(false); // apply 3D secure v2
        $browerJSEnable = $this->scopeConfig->getValue('payment/magenest_sagepay/browser_javascript_enabled');
        $applyCcv = $this->getIsApplyCvcCheck();
        $testDomain = $this->getConfigValue('test_domain');
        $liveDomain = $this->getConfigValue('live_domain');
        $testDomain = rtrim($testDomain,"/").'/';
        $liveDomain = rtrim($liveDomain,"/").'/';
        if(!$testDomain){
            $testDomain = $baseUrl;
        }

        if(!$liveDomain){
            $liveDomain = $baseUrl;
        }
        $collectRecipientDetails = boolval($this->getConfigValue('collect_recipient'));
        $sendEmail = $this->getConfigValue('send_email');
        $vendorEmail = $this->getConfigValue('vendor_email');
        if(!$vendorEmail){
            $vendorEmail = "";
        }
        $language = $this->getConfigValue('payment_language');
        return array(
            'env' => $env,
//            'protocolVersion' => 3.00,
            'protocolVersion' => ($apply3Ds2) ? 4.00 : 3.00,
            'vendorName' => $vendorName,
            //'currency' => "GBP",
            //'txType' => 'PAYMENT',
            'siteFqdns' =>
                array(
                    'live' => $liveDomain,
                    'test' => $testDomain,
                ),

            'partnerId' => '1BC70868-12A8-1383-A2FB-D7A0205DE97B',
            'vendorData' => '',
            'applyAvsCv2' => $this->convertApi3DsCcv($applyCcv),
            'apply3dSecure' => $this->convertApi3DsCcv($apply3Ds),
            'allowGiftAid' => $this->getConfigValue("gift_aid"),
            'surcharges' => $this->getSurchangeConfig(),
            'collectRecipientDetails' => $collectRecipientDetails,
            'formPassword' =>
                array(
                    'test' => $this->getTestEncryptedPassword(),
                    'live' => $this->getLiveEncryptedPassword(),
                ),
            'formSuccessUrl' => 'sagepay/form/success',
            'formFailureUrl' => 'sagepay/form/failure',
            'directSuccessUrl' => 'sagepay/direct/success',
            'directFailureUrl' => 'sagepay/direct/failure',
            'accountType' => 'E',
            'serverNotificationUrl' => 'sagepay/server/notify',
            'paypalCallbackUrl' => 'sagepay/paypal/postBack',
            'sendEmail' => $sendEmail,
            'emailMessage' => '',
            'vendorEmail' => $vendorEmail,
// Optional parameter, this value will be used to set the BillingAgreement field in the registration POST
// A default is value of 0 is used if this parameter is not included in this properties file
            'customerPasswordSalt' => '',
            'basketAsXmlDisable' => false,
            'logError' => true,
            'language' => $language,
            'website' => $baseUrl,
            'requestTimeout' => 30,
            'caCertPath' => '',
            'BrowserJavascriptEnabled' => empty($browerJSEnable) ? 0 : 1,
            'ChallengeWindowSize' => '05',
            'BrowserAcceptHeader' => isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '',
            'BrowserLanguage' => explode(',', isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : ',')[0],
            'BrowserUserAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'ClientIPAddress' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '',
            'TransType' => '01',
        );
    }

    public function decryptResp($crypt)
    {
        $formPassword = $this->getEncryptedPassword();
        $decrypt = SagepayUtil::decryptAes($crypt, $formPassword);
        $decryptArr = SagepayUtil::queryStringToArray($decrypt);
        if (!$decrypt || empty($decryptArr)) {
            throw new SagepayApiException('Invalid crypt input');
        }

        return array(
            'decrypt' => $decryptArr,
            'res' => array(
                'status' => $decryptArr['Status'],
                'vpsTxId' => $decryptArr['VPSTxId'],
                'txAuthNo' => isset($decryptArr['TxAuthNo']) ? $decryptArr['TxAuthNo'] : '',
                'Surcharge' => isset($decryptArr['Surcharge']) ? $decryptArr['Surcharge'] : '',
                'BankAuthCode' => isset($decryptArr['BankAuthCode']) ? $decryptArr['BankAuthCode'] : '',
                'DeclineCode' => isset($decryptArr['DeclineCode']) ? $decryptArr['DeclineCode'] : '',
                'GiftAid' => isset($decryptArr['GiftAid']) && $decryptArr['GiftAid'] == 1,
                'avsCv2' => isset($decryptArr['AVSCV2']) ? $decryptArr['AVSCV2'] : '',
                'addressResult' => isset($decryptArr['AddressResult']) ? $decryptArr['AddressResult'] : '',
                'postCodeResult' => isset($decryptArr['PostCodeResult']) ? $decryptArr['PostCodeResult'] : '',
                'cv2Result' => isset($decryptArr['CV2Result']) ? $decryptArr['CV2Result'] : '',
                '3DSecureStatus' => isset($decryptArr['3DSecureStatus']) ? $decryptArr['3DSecureStatus'] : '',
                'CAVV' => isset($decryptArr['CAVV']) ? $decryptArr['CAVV'] : '',
                'cardType' => isset($decryptArr['CardType']) ? $decryptArr['CardType'] : '',
                'last4Digits' => isset($decryptArr['Last4Digits']) ? $decryptArr['Last4Digits'] : '',
                'expiryDate' => isset($decryptArr['ExpiryDate']) ? $decryptArr['ExpiryDate'] : '',
                'addressStatus' => isset($decryptArr['AddressStatus']) ? $decryptArr['AddressStatus'] : '',
                'payerStatus' => isset($decryptArr['PayerStatus']) ? $decryptArr['PayerStatus'] : ''
            )
        );
    }

    public function getTransactionDetail($transactionId, $order = null)
    {
        $mode = $this->scopeConfig->getValue('payment/magenest_sagepay/test');
        if ($mode == 1) {
            $url = 'https://test.sagepay.com/access/access.htm';
        } elseif ($mode == 0) {
            $url = 'https://live.sagepay.com/access/access.htm';
        }
        $vpstxid = $transactionId;
        $command = 'getTransactionDetail';
        $vendorName = $this->getConfigValue('vendor_name', false, $order);
        $userName = $this->scopeConfig->getValue('payment/magenest_sagepay/user');
        $password = $this->scopeConfig->getValue('payment/magenest_sagepay/password');
        $password = $this->_encryptor->decrypt($password);
        $signature = $this->getXmlSignature($command, $vpstxid, $vendorName, $userName, $password);
//
        $xml = '';
        $xml .= '<vspaccess>';
        $xml .= '<command>' . $command . '</command>';
        $xml .= '<vendor>' . $vendorName . '</vendor>';
        $xml .= '<user>' . $userName . '</user>';
        $xml .= '<vpstxid>' . $transactionId . '</vpstxid>';
        $xml .= '<signature>' . $signature . '</signature>';
        $xml .= '</vspaccess>';
        $response = $this->sendFormRequest($url, 'XML=' . $xml);
        return $response;
    }

    public function getXmlSignature($command, $vpstxid, $vendorName, $userName, $password)
    {
        $params = '<vpstxid>' . $vpstxid . '</vpstxid>';
        $xml = '<command>' . $command . '</command>';
        $xml .= '<vendor>' . $vendorName . '</vendor>';
        $xml .= '<user>' . $userName . '</user>';
        $xml .= $params;
        $xml .= '<password>' . $password . '</password>';
        return hash('md5', $xml);

    }

    public function arrayToQueryParams($postData)
    {
        $post_data_string = '';
        foreach ($postData as $_key => $_val) {
            $post_data_string .= $_key . '=' . urlencode(mb_convert_encoding($_val, 'ISO-8859-1', 'UTF-8')) . '&';
        }
        return $post_data_string;
    }

    public function refund($payment, $amount)
    {
        $mode = $this->scopeConfig->getValue('payment/magenest_sagepay/test');
        if ($mode == 1) { // test mode =  1
            $url = self::SAGEPAY_SHARED_REFUND_TRANSACTION_TEST;
        } elseif ($mode == 0) { //live mode = 0
            $url = self::SAGEPAY_SHARED_REFUND_TRANSACTION_LIVE;
        }
        $order = $payment->getOrder();
        $transaction = $this->getTransactionDetail($payment->getAdditionalInformation('sagepay_transaction_id'), $order);
        $error = isset($transaction['error']) ? $transaction['error'] : '';
        $this->sageLogger->debug(var_export($transaction, true));
        if($error) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($error)
            );
        }
        $payload['VPSProtocol'] = $this->getSageApiConfigArray()['protocolVersion'];
        $payload['TxType'] = SageHelper::SAGE_PAY_TYPE_REFUND;
        $payload['Vendor'] = $this->getSageApiConfigArray()['vendorName'];
        $payload['VendorTxCode'] = $this->generateVendorTxCode($order->getIncrementId(), "Refund", $order);
        $payload['Amount'] = number_format($amount, 2, '.', '');
        $payload['Currency'] = (string)$transaction['currency'];
        $payload['Description'] = 'Refund';
        $payload['RelatedVPSTxId'] = (string)$transaction['vpstxid'];
        $payload['RelatedVendorTxCode'] = (string)$transaction['vendortxcode'];
        $payload['RelatedSecurityKey'] = (string)$transaction['securitykey'];
        $payload['RelatedTxAuthNo'] = (string)$transaction['vpsauthcode'];

        $payload = $this->arrayToQueryParams($payload);
        $response = $this->sendFormRequest($url, $payload);
        $this->sageLogger->debug(var_export($response, true));
        $response = $this->parseResponseData($response);
        if(array_key_exists("Status", $response) && ($response['Status']=='OK')){
            //refund success
            $transactionId = isset($response['VPSTxId'])?$response['VPSTxId']:"";
            $transactionId = str_replace(["{", "}"], "", $transactionId);
            $payment->setTransactionId($transactionId);
            $payment->setParentTransactionId($payment->getAdditionalInformation('transaction_id'));
        }else{
            throw new \Magento\Framework\Exception\LocalizedException(__("Refund Error"));
        }
        return $response;
    }

    public function releaseDeferredTransaction($transaction, $amount)
    {
        $mode = $this->scopeConfig->getValue('payment/magenest_sagepay/test');
        if ($mode == 1) {
            $url = self::SAGEPAY_SHARED_RELEASE_TRANSACTION_TEST;
        } elseif ($mode == 0) {
            $url = self::SAGEPAY_SHARED_RELEASE_TRANSACTION_LIVE;
        }
        $payload['VPSProtocol'] = $this->getSageApiConfigArray()['protocolVersion'];
        $payload['TxType'] = self::SAGE_PAY_TYPE_RELEASE;
        $payload['Vendor'] = $this->getSageApiConfigArray()['vendorName'];
        $payload['VendorTxCode'] = (string)$transaction['vendortxcode'];
        $payload['ReleaseAmount'] = number_format($amount, 2, '.', '');
        $payload['VPSTxId'] = (string)$transaction['vpstxid'];
        $payload['SecurityKey'] = (string)$transaction['securitykey'];
        $payload['TxAuthNo'] = (string)$transaction['vpsauthcode'];

        $payload = $this->arrayToQueryParams($payload);
        $response = $this->sendFormRequest($url, $payload);
        return $response;
    }

    public function voidTransaction($payment)
    {
        $mode = $this->scopeConfig->getValue('payment/magenest_sagepay/test');
        if ($mode == 1) {
            $url = self::SAGEPAY_SHARED_VOID_TRANSACTION_TEST;
        } elseif ($mode == 0) {
            $url = self::SAGEPAY_SHARED_VOID_TRANSACTION_LIVE;
        }
        $transaction = $this->getTransactionDetail($payment->getAdditionalInformation('transaction_id'));
        $payload['VPSProtocol'] = $this->getSageApiConfigArray()['protocolVersion'];
        $payload['TxType'] = self::SAGE_PAY_TYPE_VOID;
        $payload['Vendor'] = $this->getSageApiConfigArray()['vendorName'];
        $payload['VendorTxCode'] = (string)$transaction['vendortxcode'];
        $payload['VPSTxId'] = (string)$transaction['vpstxid'];
        $payload['SecurityKey'] = (string)$transaction['securitykey'];
        $payload['TxAuthNo'] = (string)$transaction['vpsauthcode'];

        $response = $this->sendFormRequest($url, $payload);
        return $response;
    }

    public function parseResponseData($arr){
        $dataReturn = [];
        foreach ($arr as $value){
            $_value = explode("=", $value);
            if(isset($_value[0]) && isset($_value[1])){
                $dataReturn[$_value[0]] = $_value[1];
            }
        }
        return $dataReturn;
    }

    public function getSurchangeConfig(){
        $data = [];
        $surchangeConfig = $this->scopeConfig->getValue('payment/magenest_sagepay/require/surcharge_config',ScopeInterface::SCOPE_STORE);
        $values = json_decode($surchangeConfig,true);
        $usedType = [];
        if($values){
            foreach ($values as $surchangeConfig){
                $surchangeElement = $this->createSurchangeElement($surchangeConfig);
                if($surchangeElement && !isset($usedType[$surchangeElement['paymentType']])){
                    $data[] = $surchangeElement;
                    $usedType[$surchangeElement['paymentType']] = 1;
                }

            }
        }
        return $data;
    }

    protected function createSurchangeElement($surchangeConfig)
    {
        if ($surchangeConfig['payment_type'] && $surchangeConfig['surchange_type'] && $surchangeConfig['value']) {
            return [
                'paymentType' => $surchangeConfig['payment_type'],
                $surchangeConfig['surchange_type'] => $surchangeConfig['value']
            ];
        }
        return false;
    }

    public function getPaymentProfileMode()
    {
        return $this->scopeConfig->getValue('payment/magenest_sagepay_server/payment_profile');
    }

    public function parseErrorResponse($arr = []){
        $arr = json_decode(json_encode($arr), true);
        $result = array();
        array_walk_recursive($arr, function($v) use (&$result) {
            $result[] = $v;
        });
        return implode('. ', $result);
        //var_dump($result);
    }

    public function getPaypalBillingAgreement(){
        return $this->scopeConfig->getValue('payment/magenest_sagepay_paypal/billing_agreement');
    }

    /**
     * @param array $data
     * @param \Magento\Quote\Model\Quote $quote
     * @param $type
     * @return array
     */
    public function getResponseData($data, $quote, $type){
        if(is_array($data)) {
            $transactionId = isset($data['VPSTxId']) ? $data['VPSTxId'] : '';
            $transactionId = str_replace(["{", "}"], "", $transactionId);
            $arrData = [
                'transaction_id' => $transactionId,
                'vendor_tx_code' => isset($data['VendorTxCode']) ? $data['VendorTxCode'] : '',
                'transaction_type' => $type,
                'transaction_status' => isset($data['Status']) ? $data['Status'] : '',
                'card_secure' => isset($data['3DSecureStatus']) ? $data['3DSecureStatus'] : '',
                'status_detail' => isset($data['StatusDetail']) ? $data['StatusDetail'] : '',
                'customer_id' => $quote->getCustomerId(),
                'customer_email' => $quote->getCustomerEmail()?$quote->getCustomerEmail():$quote->getBillingAddress()->getEmail(),
                'quote_id' => $quote->getId(),
                'response_data' => json_encode($data)
            ];
            return $arrData;
        }
        return [];
    }

    public function getPiResponseData($response, $quote, $type){
        $responseData = json_decode(json_encode($response), true);
        $threeD = isset($responseData['3DSecure']) ? $responseData['3DSecure'] : [];
        $dsecureStatus = isset($threeD['status']) ? $threeD['status'] : '';
        if(is_array($responseData)){
            foreach ($responseData as $k => $v){
                if(is_array($v)){
                    unset($responseData[$k]);
                }
            }
        }
        $data = $responseData;
        if(is_array($data)) {
            $transactionId = isset($data['transactionId']) ? $data['transactionId'] : '';
            $arrData = [
                'transaction_id' => $transactionId,
                'transaction_type' => $type,
                'transaction_status' => isset($data['status']) ? $data['status'] : '',
                'card_secure' => $dsecureStatus,
                'status_detail' => isset($data['statusDetail']) ? $data['statusDetail'] : '',
                'customer_id' => $quote->getCustomerId(),
                'customer_email' => $quote->getCustomerEmail()?$quote->getCustomerEmail():$quote->getBillingAddress()->getEmail(),
                'quote_id' => $quote->getId(),
                'response_data' => json_encode($data)
            ];
            return $arrData;
        }
        return [];
    }

    /**
     * Convert a object to a data object (used for repairing __PHP_Incomplete_Class objects)
     * @param array $d
     * @return array|mixed|object
     */
    function arrayObjectToStdClass($d = [])
    {
        /**
         * If json_decode and json_encode exists as function, do it the simple way.
         * http://php.net/manual/en/function.json-encode.php
         */
        if (function_exists('json_decode') && function_exists('json_encode')) {
            return json_decode(json_encode($d), true);
        }
        $newArray = array();
        if (is_array($d) || is_object($d)) {
            foreach ($d as $itemKey => $itemValue) {
                if (is_array($itemValue)) {
                    $newArray[$itemKey] = (array)$this->arrayObjectToStdClass($itemValue);
                } elseif (is_object($itemValue)) {
                    $newArray[$itemKey] = (object)(array)$this->arrayObjectToStdClass($itemValue);
                } else {
                    $newArray[$itemKey] = $itemValue;
                }
            }
        }
        return $newArray;
    }
}
