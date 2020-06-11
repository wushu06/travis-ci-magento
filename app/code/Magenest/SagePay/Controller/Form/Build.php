<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Form;

use Magenest\SagePay\Helper\SagepayAPI;
use Magenest\SagepayLib\Classes\Constants;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\QuoteValidator;
use Magenest\SagepayLib\Classes\SagepayApiException;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magenest\SagepayLib\Classes\SagepaySettings;

class Build extends Action
{
    protected $coreRegistry;

    protected $_formKeyValidator;

    protected $checkoutSession;

    protected $sageHelper;

    protected $sageLogger;

    protected $quoteValidator;

    protected $customerManagement;

    protected $dataHelper;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        CustomerManagement $customerManagement,
        QuoteValidator $quoteValidator,
        \Magenest\SagePay\Helper\Data $dataHelper
    ) {
        $this->coreRegistry = $registry;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->sageHelper = $sageHelper;
        $this->sageLogger = $sageLogger;
        $this->quoteValidator = $quoteValidator;
        $this->customerManagement = $customerManagement;
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        try {
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new SagepayApiException("Invalid Form Key");
            }
            if ($this->getRequest()->isAjax()) {
                $quote = $this->checkoutSession->getQuote();
                $this->quoteValidator->validateBeforeSubmit($quote);
                if (!$quote->getCustomerIsGuest()) {
                    if ($quote->getCustomerId()) {
                        if(method_exists($this->customerManagement,'validateAddresses')) {
                            $this->customerManagement->validateAddresses($quote);
                        }
                    }
                }
                $billingAddress = json_decode($this->getRequest()->getParam('billing_address'), true);
                $shippingAddress = json_decode($this->getRequest()->getParam('shipping_address'), true);
                $guestEmail = $this->getRequest()->getParam('guest_email');

                $quoteDetails = $this->sageHelper->getPaymentDetail($quote, $billingAddress, $shippingAddress,
                    $guestEmail);
                $config = [
                    'currency' => strtoupper($quote->getBaseCurrencyCode()),
                    'txType' => $this->sageHelper->getSageFormPaymentAction()
                ];
                $apiConfig = array_merge_recursive($this->sageHelper->getSageApiConfigArray(), $config);
                $sageConfig = SagepaySettings::getInstance($apiConfig, false);
                $sageApi = new SagepayAPI($sageConfig, Constants::SAGEPAY_FORM);
                $api = $sageApi->buildApi($quote, $quoteDetails);
                if ($api) {
                    $request = $api->createRequest();
                    $vendorTxCode = $api->getData()['VendorTxCode'];
                    $quote->setPaymentMethod('magenest_sagepay_form');
                    $quote->getPayment()->setAdditionalInformation("vendor_tx_code", $vendorTxCode);
                    $quote->getPayment()->importData(['method' => 'magenest_sagepay_form']);
                    $quote->setIsActive(false);
                    $quote->save();
                    $queryString = htmlspecialchars(rawurldecode(utf8_encode($api->getQueryData())));
                    $this->sageLogger->debug("Begin SagePay Form");
                    $this->sageLogger->debug($queryString);
                    $result->setData([
                        'success' => true,
                        'request' => $request,
                        'purchaseUrl' => $sageConfig->getPurchaseUrl(Constants::SAGEPAY_FORM, $sageConfig->getEnv()),
                        //'string' => $queryString
                    ]);
                }else {
                    $result->setData([
                        'error' => true,
                        'message' => __("Payment Request Error")
                    ]);
                }
            }else {
                $result->setData([
                    'error' => true,
                    'message' => __("Invalid request")
                ]);
            }
        }
        catch (SagepayApiException $e){
            $this->dataHelper->debugException($e);
            $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
        catch (\Magento\Framework\Validator\Exception $e){
            $this->dataHelper->debugException($e);
            $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
        catch (LocalizedException $e){
            $this->dataHelper->debugException($e);
            $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
        catch (\Exception $e){
            $this->dataHelper->debugException($e);
            $result->setData([
                'error' => true,
                'message' => __("Payment error")
            ]);
        }
        finally{
            return $result;
        }
    }
}
