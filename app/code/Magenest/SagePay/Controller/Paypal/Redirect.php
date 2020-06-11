<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Paypal;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;

class Redirect extends Action{

    protected $checkoutSession;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $session
    )
    {
        $this->checkoutSession = $session;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();

            if($order->getState() == Order::STATE_NEW) {
                $redirectUrl = $order->getPayment()->getAdditionalInformation('paypal_redirect_url');
                if ($redirectUrl) {
                    return $this->_redirect($redirectUrl);
                }
            }else{
                return $this->_redirect('sales/order/history');
            }
        }catch (\Exception $exception){
            $this->messageManager->addError(__('Redirect Error'));
        }
        return $this->_redirect("checkout/onepage/success");
    }
}