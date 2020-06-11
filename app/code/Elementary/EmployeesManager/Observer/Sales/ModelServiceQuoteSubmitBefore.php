<?php


namespace Elementary\EmployeesManager\Observer\Sales;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ModelServiceQuoteSubmitBefore
 *
 * @package Elementary\EmployeesManager\Observer\Sales
 */
class ModelServiceQuoteSubmitBefore implements \Magento\Framework\Event\ObserverInterface
{
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Json $serializer = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            $quote = $observer->getQuote();
            $order = $observer->getOrder();
            $quoteItems = [];
            // Map Quote Item with Quote Item Id
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $quoteItems[$quoteItem->getId()] = $quoteItem;
            }
            foreach ($order->getAllVisibleItems() as $orderItem) {
                $quoteItemId = $orderItem->getQuoteItemId();
                $quoteItem = $quoteItems[$quoteItemId];
                $additionalOptions = $quoteItem->getOptionByCode('additional_options');
                if ($additionalOptions) {
                    // Get Order Item's other options
                    $options = $orderItem->getProductOptions();
                    // Set additional options to Order Item
                    $options['additional_options'] = $this->serializer->unserialize($additionalOptions->getValue());
                    $orderItem->setProductOptions($options);
                }
            }
        } catch (\Exception $e) {
            // catch error if any

        }
    }
}

