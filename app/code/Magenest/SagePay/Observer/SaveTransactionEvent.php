<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\Sagepay\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveTransactionEvent implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Payment $payment
         */
        try {
            $transaction = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magenest\SagePay\Model\Transaction');
            $transactionData = $observer->getTransactionData();
            $transactionId = isset($transactionData['transaction_id']) ? $transactionData['transaction_id'] : false;
            $vendorTxCode = isset($transactionData['vendor_tx_code']) ? $transactionData['vendor_tx_code'] : false;
            if ($transactionId) {
                /** @var \Magenest\SagePay\Model\Transaction $transactionModel */
                $transactionModel = $transaction->load($transactionId, "transaction_id");
            }
            if ($vendorTxCode) {
                /** @var \Magenest\SagePay\Model\Transaction $transactionModel */
                $transactionModel = $transaction->load($vendorTxCode, "vendor_tx_code");
            }
            if (isset($transactionModel)) {
                if (!$transactionModel->getId() || $transactionModel->getTransactionId() == '') {
                    $transactionModel->setData("transaction_id", $transactionId);
                }
                $transactionModel->addData($transactionData);
                if ($transactionModel->getData() != $transactionModel->getOrigData()) {
                    $transactionModel->save();
                }
            }
        } catch (\Exception $e) {
        }
    }
}