<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Adminhtml\Transaction;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

class Restore extends \Magento\Backend\App\Action
{
    protected $transactionFactory;
    protected $quoteFactory;
    protected $quoteManagement;
    protected $orderSender;
    protected $orderFactory;

    public function __construct(
        Action\Context $context,
        \Magenest\SagePay\Model\TransactionFactory $transactionFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->orderFactory = $orderFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $transaction = $this->transactionFactory->create()->load($id);
                if(strtolower($transaction->getTransactionStatus()) == 'ok') {
                    $quoteId = $transaction->getQuoteId();
                    $quote = $this->quoteFactory->create()->load($quoteId);
                    if ((!$quote->getId()) || (!$quoteId)) {
                        throw new LocalizedException(__("Cannot find quote"));
                    }
                    $quote->setIsActive(true)->save();
                    $orderId = $this->quoteManagement->placeOrder($quote->getId(), $quote->getPayment());
                    $order = $this->orderFactory->create()->load($orderId);
                    if ($order->getCanSendNewEmailFlag()) {
                        try {
                            $this->orderSender->send($order);
                        } catch (\Exception $e) {
                        }
                    }
                    $transaction->setOrderId($orderId)->save();
                    $this->messageManager->addSuccessMessage(__("Restored order %1", $order->getIncrementId()));
                }else{
                    $this->messageManager->addErrorMessage(__("Can't restore this transaction"));
                }
                return $this->_redirect('sagepay/transaction');
            }
            catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __("Error"));
            }
        }
        $this->messageManager->addErrorMessage(__("Can't restore this transaction"));

        return $this->_redirect('sagepay/transaction/index');
    }
}
