<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Model;

use Magenest\SagePay\Model\ResourceModel\Card as Resource;
use Magenest\SagePay\Model\ResourceModel\Card\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magenest\SagePay\Helper\Subscription;

class Card extends AbstractModel
{
    protected $_eventPrefix = 'card_';

    public function __construct(
        Context $context,
        Registry $registry,
        Resource $resource,
        Collection $resourceCollection,
        $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function addCard($customerId, $cardId, $last4)
    {
        $data = [
            'customer_id' => $customerId,
            'card_id' => $cardId,
            'last_4' => $last4
        ];
        $this->setData($data)->save();
    }

    public function loadCards($customerId)
    {
        return $this->getCollection()->addFieldToFilter('customer_id', $customerId)->getData();
    }

    public function hasCard($customerId)
    {
        return $this->getCollection()->addFieldToFilter('customer_id', $customerId)->getSize() != 0;
    }

    public function isOwn($customerId)
    {
        return $this->getData('customer_id') == $customerId;
    }

    public function getAvailableStatuses()
    {
        return [
            Subscription::SUBS_STAT_ACTIVE_CODE => Subscription::SUBS_STAT_ACTIVE_TEXT,
            Subscription::SUBS_STAT_INACTIVE_CODE => Subscription::SUBS_STAT_INACTIVE_TEXT,
            Subscription::SUBS_STAT_END_CODE => Subscription::SUBS_STAT_END_TEXT,
            Subscription::SUBS_STAT_CANCELLED_CODE => Subscription::SUBS_STAT_CANCELLED_TEXT
        ];
    }
}
