<?php

/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 11/10/2019
 * Time: 10:40
 */


namespace Magenest\SagePay\Model\Api;


use Magenest\SagePay\Helper\SagepayAPI;
use Magenest\SagepayLib\Classes\Constants;
use Magenest\SagepayLib\Classes\SagepayApiException;
use Magenest\SagepayLib\Classes\SagepaySettings;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\QuoteValidator;

class BuildForm implements \Magenest\SagePay\Api\BuildForm
{
    protected $coreRegistry;

    protected $checkoutSession;

    protected $sageHelper;

    protected $sageLogger;

    protected $quoteValidator;

    protected $customerManagement;

    protected $dataHelper;

    protected $cartRepository;

    protected $_transactionFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        CustomerManagement $customerManagement,
        QuoteValidator $quoteValidator,
        \Magenest\SagePay\Helper\Data $dataHelper,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magenest\SagePay\Model\TransactionFactory $transactionFactory
    )
    {
        $this->coreRegistry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->sageHelper = $sageHelper;
        $this->sageLogger = $sageLogger;
        $this->quoteValidator = $quoteValidator;
        $this->customerManagement = $customerManagement;
        $this->dataHelper = $dataHelper;
        $this->cartRepository = $cartRepository;
        $this->_transactionFactory = $transactionFactory;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function buildFormSubmit($data)
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $this->quoteValidator->validateBeforeSubmit($quote);
            if (!$quote->getCustomerIsGuest()) {
                if ($quote->getCustomerId()) {
                    if (method_exists($this->customerManagement, 'validateAddresses')) {
                        $this->customerManagement->validateAddresses($quote);
                    }
                }
            }
            $billingAddress = json_decode(isset($data['billing_address']) ? $data['billing_address'] : '', true);
            $shippingAddress = json_decode(isset($data['shipping_address']) ? $data['shipping_address'] : '', true);
            $guestEmail = $data['guest_email'];
            $quoteId = $data['quote_id'];

            $quoteDetails = $this->sageHelper->getPaymentDetail($quote, $billingAddress, $shippingAddress, $guestEmail);
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
                $this->cartRepository->save($quote);
                $queryString = htmlspecialchars(rawurldecode(utf8_encode($api->getQueryData())));
                $this->sageLogger->debug("Begin SagePay Form");
                $this->sageLogger->debug($queryString);
                $result = json_encode([
                    'success' => true,
                    'request' => $request,
                    'purchaseUrl' => $sageConfig->getPurchaseUrl(Constants::SAGEPAY_FORM, $sageConfig->getEnv()),
                    //'string' => $queryString
                ]);
            } else {
                $result = json_encode([
                    'error' => true,
                    'message' => __("Payment Request Error")
                ]);
                $quote->setIsActive(true);
                $this->cartRepository->save($quote);
            }

            $transactionModel = $this->_transactionFactory->create();
            $transactionModel->addData([
                'transaction_id' => '',
                'transaction_type' => 'Form',
                'transaction_status' => 'Pending response',
                'quote_id' => $quote->getId(),
                'customer_id' => $quote->getCustomerId(),
                'customer_email' => $quote->getCustomerEmail() ? $quote->getCustomerEmail() : $quote->getBillingAddress()->getEmail(),
                'vendor_tx_code' => $vendorTxCode
            ]);
            $transactionModel->save();

        } catch (SagepayApiException $e) {
            $this->dataHelper->debugException($e);
            $result = json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
            $quote->setIsActive(true);
            $this->cartRepository->save($quote);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $this->dataHelper->debugException($e);
            $result = json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
            $quote->setIsActive(true);
            $this->cartRepository->save($quote);
        } catch (LocalizedException $e) {
            $this->dataHelper->debugException($e);
            $result = json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
            $quote->setIsActive(true);
            $this->cartRepository->save($quote);
        } catch (\Exception $e) {
            $this->dataHelper->debugException($e);
            $result = json_encode([
                'error' => true,
                'message' => __("Payment error")
            ]);
            $quote->setIsActive(true);
            $this->cartRepository->save($quote);
        } finally {
            return $result;
        }
    }
}