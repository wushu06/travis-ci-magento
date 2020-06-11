<?php

/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 07/09/2019
 * Time: 15:24
 */


namespace Magenest\SagePay\Model;


use Magenest\SagePay\Api\ThreeDInfo;

class ThreeDSecure implements ThreeDInfo
{
    protected $formKeyValidator;

    protected $checkoutSession;

    protected $_request;

    public function __construct(
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->_request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * @return mixed
     */
    public function get3DInfo()
    {
        try {
            $data = $this->get3DSecureResponseData();
        } catch (\Exception $e) {
            return json_encode([
                'error' => true,
                'message' => __("Payment exception: " . $e->getMessage())
            ]);
        }

        return json_encode($data);

    }

    public function get3DSecureResponseData()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();
        $data = json_decode($payment->getAdditionalInformation('3d_secure_response'), true);
        if ($data) {
            $data['is3dSecure'] = true;
            $data['success'] = true;
            $data['threeDSSessionData'] = isset($data['VPSTxId']) ? $data['VPSTxId'] : '';
            return $data;
        } else {
            return [
                'is3dSecure' => false,
                'success' => true
            ];
        }
    }

}