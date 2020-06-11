<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Cron;

use Magenest\SagePay\Helper\Subscription;

class Daily
{
    protected $_profileFactory;

    public function __construct(
        \Magenest\SagePay\Model\ProfileFactory $profileFactory,
        \Magenest\SagePay\Helper\Logger $sageLogger
    ) {
        $this->_profileFactory = $profileFactory;
        $this->sageLogger = $sageLogger;
    }

    public function execute()
    {

        /** @var \Magenest\SagePay\Model\Profile $profileModel */
        $profileModel = $this->_profileFactory->create();
        $allIds = $profileModel
            ->getCollection()
            ->addFieldToFilter('next_billing', date('Y-m-d'))
            ->addFieldToFilter('status', Subscription::SUBS_STAT_ACTIVE_CODE)
            ->getAllIds();
        $this->sageLogger->debug("sage cron begin");
        foreach ($allIds as $id) {
            /** @var \Magenest\SagePay\Model\Profile $profile */
            $profile = $this->_profileFactory->create()->load($id);
            $profile->reOrder();
        }
        $this->sageLogger->debug("sage cron end");
    }
}
