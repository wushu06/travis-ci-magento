<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Helper;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Helper\Context;

class Subscription extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SUBS_STAT_ACTIVE_CODE = 0;
    const SUBS_STAT_INACTIVE_CODE = 1;
    const SUBS_STAT_END_CODE = 2;
    const SUBS_STAT_CANCELLED_CODE = 3;
    const SUBS_STAT_ACTIVE_TEXT = "active";
    const SUBS_STAT_INACTIVE_TEXT = "inactive";
    const SUBS_STAT_END_TEXT = "end";
    const SUBS_STAT_CANCELLED_TEXT = "cancelled";

    protected $quoteFactory;
    protected $productFactory;
    protected $customerFactory;
    protected $customerRepository;

    public function __construct(
        Context $context,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        CustomerRepository $customerRepository
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Order\Item $item
     */
    public function getQuoteAmountForSubscriptionItem($order, $item)
    {
        $shippingAddress = $order->getShippingAddress();

        $orderData = [
            'currency_id' => $order->getQuoteCurrencyCode(),
            'email' => $order->getCustomerEmail(),
            'items' => [
                [
                    'product_id' => $item->getProduct()->getId(),
                    'qty' => $item->getQtyOrdered(),
                    'price' => $item->getPrice()
                ]
            ]
        ];

        if ($shippingAddress) {
            $streetString = '';
            $street = $shippingAddress->getStreet();
            if (isset($street[0])) {
                $streetString .= $street[0];
            }
            if (isset($street[1])) {
                $streetString .= $street[1];
            }

            $orderData['shipping_address'] = [
                'firstname' => $shippingAddress->getFirstname(), //address Details
                'lastname' => $shippingAddress->getLastname(),
                'street' => $streetString,
                'city' => $shippingAddress->getCity(),
                'country_id' => $shippingAddress->getCity(),
                'region' => $shippingAddress->getRegion(),
                'postcode' => $shippingAddress->getPostcode(),
                'telephone' => $shippingAddress->getTelephone(),
                'fax' => $shippingAddress->getFax(),
                'save_in_address_book' => 1
            ];
        }

        $store = $order->getStore();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $websiteId = $store->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']); // load customer by email address

        $quote = $this->quoteFactory->create();
        $quote->setStore($store);
        $quote->setQuoteCurrencyCode($order->getOrderCurrencyCode());

        if (!$customer->getEntityId()) {
            // customer not exist
            $quote->setCustomerEmail($order->getCustomerEmail());
            $quote->setCustomerFirstname($order->getCustomerFirstname());
            $quote->setCustomerGender($order->getCustomerGender());
            $quote->setCustomerLastname($order->getCustomerLastname());
            $quote->setCustomerMiddlename($order->getCustomerMiddlename());
            $quote->setCustomerIsGuest($order->getCustomerIsGuest());
            $quote->setCustomerPrefix($order->getCustomerPrefix());
            $quote->setCustomerSuffix($order->getCustomerSuffix());
        } else {
            $customer = $this->customerRepository->getById($customer->getEntityId());
            $quote->assignCustomer($customer); //Assign quote to customer
        }

        //add items in quote
        foreach ($orderData['items'] as $item) {
            $product = $this->productFactory->create()->load($item['product_id']);
            $product->setPrice($item['price']);
            $quote->addProduct(
                $product,
                intval($item['qty'])
            );
        }

        //Set Address to quote
        $billingData = $order->getBillingAddress()->getData();
        $quote->getBillingAddress()->addData($billingData);
        $paymentMethod = $order->getPayment()->getMethod();

        // Collect Rates and Set Shipping & Payment Method
        if ($shippingAddress) {
            $shippingData = $order->getShippingAddress()->getData();
            $quote->getShippingAddress()->addData($shippingData);

            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($order->getShippingAddress()->getShippingMethod()); //shipping method
        }

        $quote->setInventoryProcessed(false);
        $quote->save(); //Now Save quote and your quote is ready

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        return $quote->getGrandTotal();
    }

    /**
     * @param \Magento\Sales\Model\Order\Item[] $items
     * @return bool
     */
    public function isSubscriptionItems($items)
    {
        if (!is_array($items)) {
            $items = [$items];
        }

        foreach ($items as $item) {
            $buyRequest = $item->getBuyRequest();
            $additionalOptions = $buyRequest->getData('additional_options');
            if (is_array($additionalOptions)) {
                foreach ($additionalOptions as $key => $value) {
                    if ($key == "Billing Option") {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return array
     */
    public function getSubscriptionData($item)
    {
        $return = [];
        $additionalOptions = $item->getBuyRequest()->getData('additional_options');
        foreach ($additionalOptions as $key => $value) {
            if ($key == "Billing Option") {
                // $value = "x cycles of y unit"
                $a = explode(" cycles of ", $value);
                $b = explode(" ", $a[1]);
                $return['total_cycles'] = empty($a[0]) ? "9999" : $a[0];
                $return['frequency'] = $b[0] . " " . $b[1];
                if ($b[0] > 1) {
                    $return['frequency'] .= "s";
                }
            }
        }

        return $return;
    }
}
