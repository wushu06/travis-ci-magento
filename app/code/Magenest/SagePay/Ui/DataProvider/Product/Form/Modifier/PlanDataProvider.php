<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magenest\SagePay\Model\PlanFactory;

class PlanDataProvider extends AbstractModifier
{
    protected $_locator;

    protected $_request;

    protected $_logger;

    protected $_planFactory;

    protected $_serialize;

    public function __construct(
        RequestInterface $request,
        LocatorInterface $locator,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        PlanFactory $planFactory
    ) {
        $this->_serialize = $serialize;
        $this->_planFactory = $planFactory;
        $this->_logger = $loggerInterface;
        $this->_request = $request;
        $this->_locator = $locator;
    }

    public function modifyData(array $data)
    {
        $product = $this->_locator->getProduct();
        $productId = $product->getId();

        $planModel = $this->_planFactory->create();
        $plan = $planModel->getCollection()->addFieldToFilter('product_id', $productId)->getFirstItem();
        if ($plan->getId()) {
            $isEnabled = $plan->getEnabled();

            if (!empty($plan->getSubscriptionValue())) {
                $options = $this->_serialize->unserialize($plan->getSubscriptionValue());
            } else {
                $options = [];
            }

            $data[strval($productId)]['event']['magenest_sagepay_enabled']['enable'] = $isEnabled;

            $data[strval($productId)]['event']['magenest_sagepay']['subscription_options']['subscription_options'] = $options;
        }

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
