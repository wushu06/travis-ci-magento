<?php
namespace ED\Order\Observer\Sales;

use Bodak\CheckoutCustomForm\Api\Data\CustomFieldsInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class OrderLoadAfter
 * @package ED\Order\Observer\Sales
 */
class OrderLoadAfter implements ObserverInterface
{

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();

        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->getOrderExtensionDependency();
        }


        $buyerName = $order->getData(CustomFieldsInterface::CHECKOUT_BUYER_NAME) ?: 'null';
        $buyerEmail = $order->getData(CustomFieldsInterface::CHECKOUT_BUYER_EMAIL) ?: 'null';
        $purchaseNo = $order->getData(CustomFieldsInterface::CHECKOUT_PURCHASE_ORDER_NO) ?: 'null';
        $goodsMark = $order->getData(CustomFieldsInterface::CHECKOUT_GOODS_MARK) ?: 'null';
        $checkoutComment = $order->getData(CustomFieldsInterface::CHECKOUT_COMMENT) ?: 'null';

        $extensionAttributes->setCheckoutBuyerName($buyerName);
        $extensionAttributes->setCheckoutBuyerEmail($buyerEmail);
        $extensionAttributes->setCheckoutPurchaseOrderNo($purchaseNo);
        $extensionAttributes->setCheckoutGoodsMark($goodsMark);
        $extensionAttributes->setCheckoutComment($checkoutComment);
        $order->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return mixed
     */
    private function getOrderExtensionDependency()
    {
        $orderExtension = \Magento\Framework\App\ObjectManager::getInstance()->get(
            '\Magento\Sales\Api\Data\OrderExtension'
        );

        return $orderExtension;
    }
}
