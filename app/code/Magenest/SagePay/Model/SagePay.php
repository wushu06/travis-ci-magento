<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Model;

use Magenest\SagePay\Helper\SageHelper;
use Magenest\SagePay\Helper\Subscription as SubsHelper;

class SagePay extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'magenest_sagepay';

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
    protected $_isInitializeNeeded = true;

    protected $sageHelper;
    protected $subsHelper;
    protected $_profileFactory;
    protected $_cardFactory;
    protected $_curlFactory;
    protected $_customerSession;
    protected $sageLogger;
    protected $sageHelperMoto;
    protected $_serialize;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magenest\SagePay\Helper\Subscription $subscriptionHelper,
        \Magenest\SagePay\Model\ProfileFactory $profileFactory,
        \Magenest\SagePay\Model\CardFactory $cardFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Customer\Model\Session $session,
        \Magenest\SagePay\Helper\Logger $sageLogger,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magenest\SagePay\Helper\SageHelperMoto $sageHelperMoto,
        array $data = []
    )
    {
        $this->_serialize = $serialize;
        $this->_customerSession = $session;
        $this->_curlFactory = $curlFactory;
        $this->_cardFactory = $cardFactory;
        $this->_profileFactory = $profileFactory;
        $this->subsHelper = $subscriptionHelper;
        $this->sageHelper = $sageHelper;
        $this->sageHelperMoto = $sageHelperMoto;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );
        $this->sageLogger = $sageLogger;
    }

    protected $isSave = false;
    protected $reusable = false;
    protected $cardId;

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $this->sageLogger->debug("-----------------begin payment----------------");
        $infoInstance = $this->getInfoInstance();
        $additionalData = $data->getData('additional_data');

        if (array_key_exists('is_sage_subscription_payment', $additionalData)
            && $additionalData['is_sage_subscription_payment'] === true
        ) {
            $infoInstance->setAdditionalInformation('is_sage_subscription_payment', true);

            return $this;
        } else {
            $infoInstance->setAdditionalInformation('is_sage_subscription_payment', false);
        }

        $saveId = isset($additionalData['selected_card']) ? $additionalData['selected_card'] : "0";
        $cardId = isset($additionalData['card_identifier']) ? $additionalData['card_identifier'] : "0";
        $merchantSessionKey = isset($additionalData['merchant_sessionKey']) ? $additionalData['merchant_sessionKey'] : "0";
        $isSave = isset($additionalData['save']) ? $additionalData['save'] : "0";
        $giftAid = isset($additionalData['gift_aid']) ? $additionalData['gift_aid'] : "0";
        $infoInstance->setAdditionalInformation('saved_id', $saveId);
        $infoInstance->setAdditionalInformation('card_identifier', $cardId);
        $infoInstance->setAdditionalInformation('merchant_sessionKey', $merchantSessionKey);
        $infoInstance->setAdditionalInformation('is_save', $isSave);
        $infoInstance->setAdditionalInformation('gift_aid', $giftAid);

        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {

        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Payment $payment
         */
        $this->_debug("initialize");
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $this->_debug("orderId: " . $order->getIncrementId());

        $isSubscriptionPayment = $this->getInfoInstance()->getAdditionalInformation('is_sage_subscription_payment');
        if ($isSubscriptionPayment === true) {
            $shippingAddress = $order->getShippingAddress();
            if ($shippingAddress) {
                $stateObject->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $stateObject->setStatus('processing');
                $stateObject->setIsNotified(false);
            } else {
                $stateObject->setState(\Magento\Sales\Model\Order::STATE_COMPLETE);
                $stateObject->setStatus('complete');
                $stateObject->setIsNotified(false);
            }
            $amount = $order->getBaseGrandTotal();
            $this->capture($payment, $amount);

            return true;
        }

        $items = $order->getAllItems();
        $isSubscription = $this->subsHelper->isSubscriptionItems($items);
        if (!$this->_customerSession->isLoggedIn() && $isSubscription) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Customer is not logged in")
            );
        }

        $cardId = $payment->getAdditionalInformation('saved_id');
        if (($cardId !== '0') && ($cardId !== null) && ($cardId !== "")) {
            $card = $this->_cardFactory->create()
                ->getCollection()
                ->addFieldToFilter("card_id", $cardId)
                ->getFirstItem()
                ->getData();
            if (count($card) > 0) {
                if ($card['customer_id'] != $order->getCustomerId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Card ID exception")
                    );
                }
            }

            $this->isSave = false;
            $this->reusable = true;
            $payment->setAdditionalInformation('card_identifier', $cardId);
            $this->cardId = $cardId;
        } else {
            $this->isSave = ($payment->getAdditionalInformation('is_save') && $this->sageHelper->getCanSave());
            $this->reusable = false;
            $this->cardId = $payment->getAdditionalInformation('card_identifier');
        }
        $response = [];
        $payment->setAdditionalInformation("sage_payment_action", $paymentAction);

        if (!array_key_exists('sage_3ds_pareq', $payment->getAdditionalInformation())) {

            if ($paymentAction == 'authorize') {
                if ($isSubscription) {
                    $response = $this->_capture($payment);
                } else {
                    $response = $this->_authorize($payment);
                }
            }

            if ($paymentAction == 'authorize_capture') {
                $response = $this->_capture($payment);
            }
        }
        //$this->_debug($response);
        if (isset($response['statusCode'])) {
            $payment->setAdditionalInformation("sagepay_transaction_id", $response['transactionId']);
            //normal pay
            if ($response['statusCode'] == 0000) {
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
                $payment->setAdditionalInformation("sage_3ds_active", "false");
                $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);

                $totalDue = $order->getTotalDue();
                $baseTotalDue = $order->getBaseTotalDue();

                switch ($paymentAction) {
                    case \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER:
                        $payment->_order($baseTotalDue);
                        break;
                    case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE:
                        if ($isSubscription) {
                            $payment->setAmountAuthorized($totalDue);
                            $payment->setBaseAmountAuthorized($baseTotalDue);
                            $payment->capture(null);
                        } else {
                            $payment->authorize(true, $baseTotalDue);
                            $payment->setAmountAuthorized($totalDue);
                        }
                        break;
                    case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE:
                        $payment->setAmountAuthorized($totalDue);
                        $payment->setBaseAmountAuthorized($baseTotalDue);
                        $payment->capture(null);
                        break;
                    default:
                        break;
                }
            } //3d secure
            else {
                if ($response['statusCode'] == 2007) {
                    $order->setCanSendNewEmailFlag(false);
                    $payment->setAdditionalInformation("sage_trans_id_secure", $response['transactionId']);
                    $payment->setAdditionalInformation("sage_3ds_url", $response['acsUrl']);
                    $payment->setAdditionalInformation("sage_3ds_pareq", $response['paReq']);
                    $payment->setAdditionalInformation("sage_3ds_active", "true");
                } else {
                    $errorMsg = isset($response['statusDetail']) ? $response['statusDetail'] : "An error occurred on the server. Please try to place the order again.";
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($errorMsg)
                    );
                }
            }
        } else {
            $errors = isset($response['errors']) ? $response['errors'] : [];
            $description = $this->getErrorMsg($errors);
            if (!$description) {
                $description = "An error occurred on the server. Please try to place the order again.";
            }

            throw new \Magento\Framework\Exception\LocalizedException(
                __($this->sageHelper->parseErrorResponse($response))
            );
        }
        return parent::initialize($paymentAction, $stateObject);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _authorize($payment)
    {

        $order = $payment->getOrder();
        $sessionKey = $payment->getAdditionalInformation("merchant_sessionKey");
        $url = $this->sageHelper->getPiEndpointUrl() . '/transactions';
        //if internal order
        if ($this->_appState->getAreaCode() == 'adminhtml') {
            $payload = $this->sageHelper->buildMotoPaymentQuery(
                $order,
                $sessionKey,
                $this->cardId,
                SageHelper::SAGE_PAY_TYPE_AUTHORIZE,
                $this->isSave,
                $this->reusable
            );
        } else {
            $payload = $this->sageHelper->buildPaymentQuery(
                $order,
                $sessionKey,
                $this->cardId,
                SageHelper::SAGE_PAY_TYPE_AUTHORIZE,
                $this->isSave,
                $this->reusable
            );
        }
        $this->_debug(json_decode($payload, true));
        $response = $this->sageHelper->sendRequest($url, $payload, $order);
        $this->_debug($response);

        return $response;

    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        $isSubscriptionPayment = $this->getInfoInstance()->getAdditionalInformation('is_sage_subscription_payment');
        if ($isSubscriptionPayment === true) {
            $this->getInfoInstance()->unsAdditionalInformation();

            return parent::authorize($payment, $amount);
        }

        $order = $payment->getOrder();

        $currencyCode = $order->getBaseCurrencyCode();
        $items = $order->getAllItems();
        $isSubscription = $this->subsHelper->isSubscriptionItems($items);

        //payment successful
        $response = $this->sageHelper->arrayObjectToStdClass($this->_serialize->unserialize($payment->getAdditionalInformation('sagepay_response')));
        if (isset($response['statusCode']) && ($response['statusCode'] == 0000)) {
            $order->setCanSendNewEmailFlag(true);
            $payment->setTransactionId($response['transactionId']);
            //$payment->setParentTransactionId($response['transactionId']);
            $payment->setAdditionalInformation("sage_trans_id", $response['transactionId']);
            $payment->setIsTransactionClosed(0);
            $cardSecure = (array)$response['3DSecure'];
            if ($this->checkShouldSaveCard($order)) {
                $customerId = $order->getCustomerId();
                $this->sageHelper->saveCard($customerId, $response['paymentMethod']['card']);
            }
            if ($isSubscription) {
                $subsPlandata = $this->subsHelper->getSubscriptionData($items[0]);
                /** @var \Magenest\SagePay\Model\Profile $profileModel */
                $profileModel = $this->_profileFactory->create();
                $subsData = [
                    'transaction_id' => $response['transactionId'],
                    'order_id' => $order->getIncrementId(),
                    'customer_id' => $order->getCustomerId(),
                    'status' => SubsHelper::SUBS_STAT_ACTIVE_CODE,
                    'amount' => $amount,
                    'total_cycles' => $subsPlandata['total_cycles'],
                    'currency' => $currencyCode,
                    'frequency' => $subsPlandata['frequency'],
                    'remaining_cycles' => --$subsPlandata['total_cycles'],
                    'start_date' => date('Y-m-d'),
                    'last_billed' => date('Y-m-d'),
                    'next_billing' => date('Y-m-d', strtotime("+ " . $subsPlandata['frequency'])),
                    'sequence_order_ids' => ""
                ];
                $profileModel->setData($subsData)->save();
            }
        } else {
            $errorMsg = isset($response['statusDetail']) ? $response['statusDetail'] : "An error occurred on the server. Please try to place the order again.";
            throw new \Magento\Framework\Exception\LocalizedException(
                __($errorMsg)
            );
        }


        return parent::authorize($payment, $amount);
    }

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $this->sageLogger->debug(var_export($debugData, true));
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _capture($payment)
    {

        $order = $payment->getOrder();
        $sessionKey = $payment->getAdditionalInformation("merchant_sessionKey");
        $url = $this->sageHelper->getPiEndpointUrl() . '/transactions';
        if ($this->_appState->getAreaCode() == 'adminhtml') {
            $payload = $this->sageHelper->buildMotoPaymentQuery(
                $order,
                $sessionKey,
                $this->cardId,
                SageHelper::SAGE_PAY_TYPE_CAPTURE,
                $this->isSave,
                $this->reusable
            );
        } else {
            $payload = $this->sageHelper->buildPaymentQuery(
                $order,
                $sessionKey,
                $this->cardId,
                SageHelper::SAGE_PAY_TYPE_CAPTURE,
                $this->isSave,
                $this->reusable
            );
        }
        $this->_debug(json_decode($payload, true));
        $response = $this->sageHelper->sendRequest($url, $payload, $order);
        $this->_debug($response);

        return $response;

    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        $isSubscriptionPayment = $this->getInfoInstance()->getAdditionalInformation('is_sage_subscription_payment');
        if ($isSubscriptionPayment === true) {
            $this->getInfoInstance()->unsAdditionalInformation();

            return parent::capture($payment, $amount);
        }
        //have transaction id, capture with trans id
        $transactionId = $payment->getAdditionalInformation("sage_trans_id");
        if (!!$transactionId) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $messageManager = $objectManager->create("\\Magento\\Framework\\Message\\ManagerInterface");
            $order = $payment->getOrder();
            $url = $this->sageHelper->buildInstructionUrl($transactionId);
            $query = $this->sageHelper->buildInstructionQuery(
                $order,
                SageHelper::SAGE_PAY_TYPE_INSTRUCTION_RELEASE,
                $amount
            );
            $response = $this->sageHelper->sendRequest($url, $query, $order);
            $this->_debug($response);
            //capture done
            if ((isset($response['instructionType'])) && ($response['instructionType'] == 'release')) {
                $payment->setLastTransId($transactionId);
                $payment->setIsTransactionClosed(1);
                $payment->setShouldCloseParentTransaction(1);
            } else {
                //capture false
                if (isset($response['description'])) {
                    $messageManager->addErrorMessage($response['description']);
                }
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Capture exception")
                );
            }
        } else {
            //capture normal
            $order = $payment->getOrder();
            $currencyCode = $order->getBaseCurrencyCode();
            $items = $order->getAllItems();
            $isSubscription = $this->subsHelper->isSubscriptionItems($items);

            $response = $this->_serialize->unserialize($payment->getAdditionalInformation('sagepay_response'));
            //payment successful
            if (isset($response['statusCode']) && ($response['statusCode'] == 0000)) {
                $order->setCanSendNewEmailFlag(true);
                $payment->setTransactionId($response['transactionId']);
                $payment->setAdditionalInformation("sage_trans_id", $response['transactionId']);
                $payment->setIsTransactionClosed(0);
                if ($this->checkShouldSaveCard($order)) {
                    $customerId = $order->getCustomerId();
                    $paymentMethod = $this->sageHelper->arrayObjectToStdClass($response['paymentMethod']);
                    $this->sageHelper->saveCard($customerId, $paymentMethod->card);
                }
                if ($isSubscription) {
                    $subsPlandata = $this->subsHelper->getSubscriptionData($items[0]);
                    /** @var \Magenest\SagePay\Model\Profile $profileModel */
                    $profileModel = $this->_profileFactory->create();
                    $subsData = [
                        'transaction_id' => $response['transactionId'],
                        'order_id' => $order->getIncrementId(),
                        'customer_id' => $order->getCustomerId(),
                        'status' => SubsHelper::SUBS_STAT_ACTIVE_CODE,
                        'amount' => $amount,
                        'total_cycles' => $subsPlandata['total_cycles'],
                        'currency' => $currencyCode,
                        'frequency' => $subsPlandata['frequency'],
                        'remaining_cycles' => --$subsPlandata['total_cycles'],
                        'start_date' => date('Y-m-d'),
                        'last_billed' => date('Y-m-d'),
                        'next_billing' => date('Y-m-d', strtotime("+ " . $subsPlandata['frequency'])),
                        'sequence_order_ids' => ""
                    ];
                    $profileModel->setData($subsData)->save();
                }
            } else {
                $errorMsg = isset($response['statusDetail']) ? $response['statusDetail'] : "An error occurred on the server. Please try to place the order again.";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($errorMsg)
                );
            }

        }

        return parent::capture($payment, $amount);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $messageManager = $objectManager->create("\\Magento\\Framework\\Message\\ManagerInterface");
        $transactionId = $payment->getAdditionalInformation("sage_trans_id");
        $order = $payment->getOrder();
        $url = $this->sageHelper->getPiEndpointUrl();
        $url .= '/transactions';
        $payload = $this->sageHelper->buildRefundQuery($order, $transactionId, $amount);

        $response = $this->sageHelper->sendRequest($url, $payload, $order);
        $this->_debug($response);
        //refund success
        if (isset($response['statusCode']) && ($response['statusCode'] == 0000)) {
            $payment->setIsTransactionClosed(0);
            $payment->setTransactionId($response['transactionId']);
        } else {
            $messageManager->addErrorMessage($response['description']);
            if (isset($response['description'])) {
                $messageManager->addErrorMessage($response['description']);
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Refund Exception")
            );
        }


        return parent::refund($payment, $amount);
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->void($payment);

        return parent::cancel($payment); // TODO: Change the autogenerated stub
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {

        $order = $payment->getOrder();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $messageManager = $objectManager->create("\\Magento\\Framework\\Message\\ManagerInterface");
        $transactionId = $payment->getAdditionalInformation("sage_trans_id");
        $url = $this->sageHelper->buildInstructionUrl($transactionId);
        $query = $this->sageHelper->buildInstructionQuery(
            $order,
            SageHelper::SAGE_PAY_TYPE_INSTRUCTION_ABORT
        );
        $response = $this->sageHelper->sendRequest($url, $query, $order);
        $this->_debug($response);
        //cancel done
        if ((isset($response['instructionType'])) && ($response['instructionType'] == 'abort')) {
            $payment->setIsTransactionClosed(1);
            $payment->setShouldCloseParentTransaction(1);
        } else {
            $messageManager->addErrorMessage($response['description']);
            if (isset($response['description'])) {
                $messageManager->addErrorMessage($response['description']);
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Exception")
            );
        }

        return parent::void($payment); // TODO: Change the autogenerated stub
    }

    public function validate()
    {
        return true;
    }

    public function canUseInternal()
    {
        return $this->sageHelper->activeMoto();
    }

    public function hasVerification()
    {
        return true;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    private function checkShouldSaveCard($order)
    {
        $payment = $order->getPayment();
        $saveId = $payment->getAdditionalInformation('saved_id');
        $isSave = $payment->getAdditionalInformation('is_save');
        $canSave = $this->sageHelper->getCanSave();
        if (($canSave != 0) && ($this->_customerSession->isLoggedIn())) {
            if (($isSave) || ($canSave == 2)) {
                if (($saveId == 0) || ($saveId == "")) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getErrorMsg($errArr)
    {
        $msg = "";
        foreach ($errArr as $err) {
            $msg .= $err['code'] . "-" . $err['description'] . ": " . $err['property'] . " ";
        }
        return $msg;
    }
}
