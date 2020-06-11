<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Direct;

use Magenest\SagepayLib\Classes\Constants;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magenest\SagepayLib\Classes\SagepayCommon;

class PostBack extends  Action{

    protected $sagepayConfig;

    protected $sageHelper;

    protected $checkoutSession;

    protected $sageDirectModel;

    protected $orderRepository;


    /**
     * PostBack constructor.
     * @param Context $context
     * @param \Magenest\SagePay\Model\SagePayDirect $sagePayDirect
     */
    public function __construct(
        Context $context,
        \Magenest\SagePay\Model\SagePayDirect $sagePayDirect,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Session $session
    )
    {
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $session;
        $this->sageHelper = $sageHelper;
        $this->sagepayConfig = $sagePayDirect->getSagePayConfig();
        $this->sageDirectModel = $sagePayDirect;
        parent::__construct($context);
    }

    public function execute()
    {
        try{
//            $postBackResponse = $this->get3DSecureResponse();
            $order = $this->getOrder();
            $payment = $order->getPayment();
            $additionalInformation = $payment->getData('additional_information');
            $threeDSecureResponse = json_decode($additionalInformation['3d_secure_response'], true);
            if (isset($threeDSecureResponse['VPSTxId'])) {
                $vpsTxId = $threeDSecureResponse['VPSTxId'];
                $vpsTxId = preg_replace("/{/", "", $vpsTxId);
                $vpsTxId = preg_replace("/}/", "", $vpsTxId);
                $dataCallback = [
                    'VpsTxId' => $vpsTxId,
                    'CRes' => $this->getRequest()->getParam('cres')
                ];
            } else {
                $dataCallback = filter_input_array(INPUT_POST);
            }
            $postBackResponse = $this->get3DSecureResponse($dataCallback);
            $order = $this->getOrder($postBackResponse);
            if ($this->is3DSecureSuccess($postBackResponse)) {
                $this->finishOrder($order, $postBackResponse);
                $this->_redirect('checkout/onepage/success');
            } else {
                //$this->cancelOrder();
                $statusDetail = isset($postBackResponse['StatusDetail'])?$postBackResponse['StatusDetail']:__("3d secure authenticate fail");
                $this->messageManager->addError($statusDetail);
                $this->_redirect('checkout/cart');
            }
        }catch (\Exception $e){
            $this->messageManager->addErrorMessage(__("The payment is not complete: " . $e->getMessage()));
            $this->_redirect('checkout/cart');
        }
    }

    public function get3DSecureResponse($dataCallback){
//        $response = SagepayCommon::requestPost($this->sagepayConfig->getPurchaseUrl('direct3d'), filter_input_array(INPUT_POST));
        $response = SagepayCommon::requestPost($this->sagepayConfig->getPurchaseUrl('direct3d'), $dataCallback);
        $this->sageHelper->debug('3D secure postback response');
        $this->sageHelper->debug($response);
        return $response;
    }

    public function is3DSecureSuccess($response){
        return in_array($response['Status'], array(Constants::SAGEPAY_REMOTE_STATUS_AUTHENTICATED, Constants::SAGEPAY_REMOTE_STATUS_REGISTERED, Constants::SAGEPAY_REMOTE_STATUS_OK));
    }

    /**
     * @param $order Order
     * @param $response
     */
    public function finishOrder($order,$response){
//            $transactionDetail = $this->sageHelper->getTransactionDetail($transactionId);
        $payment = $order->getPayment();
        if($payment->getAdditionalInformation('is_authorize')){
            $this->sageDirectModel->authorizeAndSaveInfo($response,$payment);
        }else{
            $this->sageDirectModel->captureAndSaveInfo($response,$payment);
        }
        $this->orderRepository->save($order);
    }

    public function getOrder($response = null){
        //todo save and get the match order
        return $this->checkoutSession->getLastRealOrder();
    }

    public function cancelOrder(){
        $order = $this->getOrder();
        $order->cancel();
        $this->orderRepository->save($order);
        $this->checkoutSession->restoreQuote();
    }

}