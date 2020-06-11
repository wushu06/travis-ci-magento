<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magenest\SagePay\Model\PlanFactory;
use Psr\Log\LoggerInterface;

class Save implements ObserverInterface
{
    protected $_logger;

    protected $_request;

    protected $_planFactory;

    protected $messageManager;

    protected $_serialize;

    public function __construct(
        LoggerInterface $loggerInterface,
        RequestInterface $requestInterface,
        PlanFactory $planFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_serialize = $serialize;
        $this->_logger = $loggerInterface;
        $this->_request = $requestInterface;
        $this->_planFactory = $planFactory;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            $planModel = $this->_planFactory->create();
            $data = $this->_request->getParams();

            $product = $observer->getProduct();
            $productId = $product->getId();


            $plan = $planModel->getCollection()->addFieldToFilter('product_id', $productId)->getFirstItem();

            if(!isset($data['event'])){
                return;
            }
            $data = $data['event'];
            $result = [];

            if (array_key_exists('magenest_sagepay', $data)) {
                $newData = $data['magenest_sagepay']['subscription_options']['subscription_options'];

                if ($newData != 'false') {
                    foreach ($newData as $item) {
                        if (array_key_exists('is_delete', $item)) {
                            if ($item['is_delete']) {
                                continue;
                            }
                        }
                        array_push($result, $item);
                    }

                }
            }

            $plan->setData("enabled", $data['magenest_sagepay_enabled']['enable']);
            $plan->setData("subscription_value", $this->_serialize->serialize($result));
            $plan->setData("product_id", $productId);
            $plan->save();
        }catch (\Exception $e){
            $this->messageManager->addErrorMessage(__("Cannot save sagepay product"));
        }
    }
}
