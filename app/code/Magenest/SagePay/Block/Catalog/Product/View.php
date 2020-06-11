<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Block\Catalog\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magenest\SagePay\Model\PlanFactory;

class View extends \Magento\Catalog\Block\Product\AbstractProduct
{
    protected $_date;

    protected $_planFactory;

    protected $_serialize;

    public function __construct(
        Context $context,
        DateTime $dateTime,
        PlanFactory $planFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        array $data = []
    ) {
        $this->_serialize = $serialize;
        $this->_date = $dateTime;
        $this->_planFactory = $planFactory;
        parent::__construct($context, $data);
    }

    public function getIsSubscriptionProduct()
    {
        $product = $this->_coreRegistry->registry('current_product');
        $productId = $product->getId();

        $planModel = $this->_planFactory->create();
        $plan = $planModel->getCollection()->addFieldToFilter('product_id', $productId)->getFirstItem();

        if ($plan) {
            $value = $plan->getEnabled();

            return $value;
        }

        return false;
    }

    public function getSubscriptionOptions()
    {
        $product = $this->_coreRegistry->registry('current_product');
        $productId = $product->getId();

        $planModel = $this->_planFactory->create();
        $plan = $planModel->getCollection()->addFieldToFilter('product_id', $productId)->getFirstItem();

        if ($plan) {
            if (!empty($plan->getSubscriptionValue())) {
                $options = $this->_serialize->unserialize($plan->getSubscriptionValue());
            } else {
                $options = [];
            }
            return $options;
        }

        return [];
    }
}
