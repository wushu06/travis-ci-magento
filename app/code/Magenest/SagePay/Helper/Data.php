<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $vendorName;
    protected $isTest;
    protected $integrationKey;
    protected $integrationPassword;
    protected $minAmount;
    protected $maxAmount;
    protected $vendorCode;
    protected $currentStoreId;

    protected $_encryptor;
    protected $_curlFactory;
    protected $_storeResolver;
    protected $sageLogger;
    protected $sessionQuote;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptorInterface,
        CurlFactory $curlFactory,
        \Magento\Store\Model\StoreResolver $storeResolver,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magenest\SagePay\Helper\Logger $sageLogger
    ) {
        $this->_encryptor = $encryptorInterface;
        $this->_curlFactory = $curlFactory;
        $this->_storeResolver = $storeResolver;
        $this->sessionQuote = $sessionQuote;
        parent::__construct($context);
        $this->currentStoreId = $this->_storeResolver->getCurrentStoreId();
        $this->isTest = $this->getConfigValue('test');
        $this->vendorName = $this->getConfigValue('vendor_name');
        $this->integrationKey = $this->getConfigValue('integration_key', true);
        $this->integrationPassword = $this->getConfigValue('integration_password', true);
        $this->minAmount = $this->getConfigValue('min_order_total');
        $this->maxAmount = $this->getConfigValue('max_order_total');
        $this->vendorCode = $this->getConfigValue('vendor_code');
        $this->sageLogger = $sageLogger;
    }

    public function sendRequest($url, $payload)
    {
        $http = $this->_curlFactory->create();

        $encoded_credential = base64_encode($this->integrationKey . ':' . $this->integrationPassword);
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
            $headers,
            $payload
        );

        $response = $http->read();
        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);

        $response = (array)json_decode($response, true);

        return $response;
    }

    public function getConfigValue($value, $encrypted = false)
    {
        $configValue = $this->scopeConfig->getValue(
            'payment/magenest_sagepay/' . $value, ScopeInterface::SCOPE_WEBSITE
        );
        if(isset($this->sessionQuote)){
            $configValue = $this->scopeConfig->getValue(
            'payment/magenest_sagepay/' . $value, ScopeInterface::SCOPE_STORE, $this->sessionQuote->getStore()->getCode()
        );

        }
        if ($encrypted) {
            return $this->_encryptor->decrypt($configValue);
        } else {
            return $configValue;
        }
    }

    public function getEndpointUrl()
    {
        if ($this->isTest) {
            return 'https://test.sagepay.com/api/v1';
        } else {
            return 'https://live.sagepay.com/api/v1';
        }
    }

    public function getPiEndpointUrl()
    {
        if ($this->isTest) {
            return 'https://pi-test.sagepay.com/api/v1';
        } else {
            return 'https://pi-live.sagepay.com/api/v1';
        }
    }

    public function getMinAmount()
    {
        return $this->minAmount;
    }

    public function getMaxAmount()
    {
        return $this->maxAmount;
    }

    public function getIntegrationKey()
    {
        return $this->integrationKey;
    }

    public function getIntegrationPassword()
    {
        return $this->integrationPassword;
    }

    public function getVendorCode()
    {
        return $this->vendorCode;
    }

    public function getVendorName()
    {
        return $this->vendorName;
    }

    public function getIsTest()
    {
        return $this->isTest;
    }

    public function sendCurlRequest($url, $payload)
    {
        $http = $this->_curlFactory->create();

        $encoded_credential = base64_encode($this->integrationKey . ':' . $this->integrationPassword);
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
            $headers,
            $payload
        );

        $rawResponse = $http->read();
        $response_status = $http->getInfo(CURLINFO_HTTP_CODE);
        $http->close();

        $data = preg_split('/^\r?$/m', $rawResponse, 2);
        $data = json_decode(trim($data[1]));

        $response = [
            "status" => $response_status,
            "data" => $data
        ];

        return $response;
    }

    public function submit3D($paRes, $md)
    {
        $url = $this->getPiEndpointUrl();
        $threeDurl = $url . '/transactions/' . $md . '/3d-secure';
        $jsonBody = json_encode(["paRes" => $paRes]);
        $result = $this->sendCurlRequest($threeDurl, $jsonBody);
        if ($result["status"] == 201) {
            return $result["data"];
        } else {
            $descriptionErr = isset($result["data"]->description)?$result["data"]->description:"Payment exception";
            throw new LocalizedException(__($descriptionErr));
        }
    }

    /**
     * @var \Exception $e
     */
    public function debugException($e){
        $this->sageLogger->debug($e->getFile().":".$e->getLine().":".$e->getMessage());
    }
}
