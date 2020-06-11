<?php

namespace Cminds\MultiUserAccounts\Block\Order\Approve;

use Cminds\MultiUserAccounts\Api\SubaccountTransportRepositoryInterface;
use Cminds\MultiUserAccounts\Helper\View as ViewHelper;
use Cminds\MultiUserAccounts\Model\Config as ModuleConfig;
use Cminds\MultiUserAccounts\Model\ResourceModel\Subaccount\Collection as SubaccountCollection;
use Cminds\MultiUserAccounts\Model\ResourceModel\Subaccount\CollectionFactory as SubaccountCollectionFactory;
use Cminds\MultiUserAccounts\Model\Service\Order\ApproveRequest;
use Magento\Checkout\Block\Cart\AbstractCart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\Helper\Data as PricingHelperData;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;

/**
 * Cminds MultiUserAccounts waiting for approve orders list block.
 *
 * @category Cminds
 * @package  Cminds_MultiUserAccounts
 * @author   Piotr Pierzak <piotr@cminds.com>
 */
class Table extends AbstractCart
{
    /**
     * @var array
     */
    private $quotes;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var SubaccountCollectionFactory
     */
    private $subaccountCollectionFactory;

    /**
     * @var PricingHelperData
     */
    private $pricingHelperData;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var ViewHelper
     */
    private $viewHelper;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var ApproveRequest
     */
    private $approveRequest;

    /**
     * @var SubaccountTransportRepositoryInterface
     */
    private $subaccountTransportRepository;

    /**
     * Object initialization.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param SubaccountCollectionFactory $subaccountCollectionFactory
     * @param PricingHelperData $pricingHelperData
     * @param QuoteFactory $quoteFactory
     * @param ViewHelper $viewHelper
     * @param ModuleConfig $moduleConfig
     * @param ApproveRequest $approveRequest
     * @param SubaccountTransportRepositoryInterface $subaccountTransportRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        QuoteCollectionFactory $quoteCollectionFactory,
        SubaccountCollectionFactory $subaccountCollectionFactory,
        PricingHelperData $pricingHelperData,
        QuoteFactory $quoteFactory,
        ViewHelper $viewHelper,
        ModuleConfig $moduleConfig,
        ApproveRequest $approveRequest,
        SubaccountTransportRepositoryInterface $subaccountTransportRepository,
        array $data = []
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->subaccountCollectionFactory = $subaccountCollectionFactory;
        $this->pricingHelperData = $pricingHelperData;
        $this->quoteFactory = $quoteFactory;
        $this->viewHelper = $viewHelper;
        $this->moduleConfig = $moduleConfig;
        $this->approveRequest = $approveRequest;
        $this->subaccountTransportRepository = $subaccountTransportRepository;

        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $data
        );
    }

    /**
     * @return array|bool
     */
    public function getQuotes()
    {
        $customerId = $this->_customerSession->getCustomerId();
        if ($customerId === null) {
            return false;
        }

        if (!$this->quotes) {
            $orderAmount = 0;
            if ($this->viewHelper->isSubaccountLoggedIn()) {
                $customerIds = [$customerId];
                if ($this->viewHelper->canManageOrderApprovals()) {
                    /** @var SubaccountCollection $subaccountCollection */
                    $subaccountCollection = $this->subaccountCollectionFactory
                        ->create()
                        ->addFieldToSelect(
                            'customer_id'
                        )
                        ->addFieldToFilter(
                            'parent_customer_id',
                            $customerId
                        );

                    foreach ($subaccountCollection as $subaccount) {
                        $customerIds[] = $subaccount->getCustomerId();
                    }
                }
            } else {
                /** @var SubaccountCollection $subaccountCollection */
                $subaccountCollection = $this->subaccountCollectionFactory
                    ->create()
                    ->addFieldToSelect(
                        'customer_id'
                    )
                    ->addFieldToFilter(
                        'parent_customer_id',
                        $customerId
                    );

                foreach ($subaccountCollection as $subaccount) {
                    $customerIds[] = $subaccount->getCustomerId();
                }

            }

            if (empty($customerIds)) {
                return false;
            }

            /** @var Collection $quoteCollection */
            $quoteCollection = $this->quoteCollectionFactory->create()
                ->addFieldToSelect('*')
                ->setOrder(
                    'updated_at',
                    'desc'
                );
            $quoteCollection
                ->getSelect()
                ->where('approve_hash IS NOT NULL')
                ->where('subaccount_id in(' . implode(',', $customerIds) . ')');

            $authorizationRequired = $this->moduleConfig
                ->isOrderApprovalRequestAuthorizationRequired();

            if ($authorizationRequired === false
                && $this->viewHelper->isSubaccountLoggedIn() === true
                && $orderAmount
            ) {
                $quoteCollection
                    ->getSelect()
                    ->where('grand_total <= ?', $orderAmount);
            }

            if ($authorizationRequired === true
                && $this->viewHelper->isSubaccountLoggedIn() === true // why should we skip this for nested?
            ) {
                foreach ($quoteCollection as $key => $quote) {
                    if ($quote->getSubaccountId() === null) {
                        continue;
                    }

                    // In case when order is authorized,
                    // let's check if current user can approve it.
                    if ($quote->getIsAuthorized()) {
                        if ($orderAmount < (float)$quote->getBaseGrandTotal()) {
                            $quoteCollection->removeItemByKey($key);
                        }

                        continue;
                    }
                }
            }

            $this->quotes = $quoteCollection;
        }

        return $this->quotes;
    }

    /**
     * @return Table
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getQuotes()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'subaccounts.order.approve.waiting.pager'
            )->setCollection(
                $this->getQuotes()
            );
            $this->setChild('pager', $pager);
            $this->getQuotes()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param   QuoteModel $quoteModel
     *
     * @return  string
     */
    public function getApproveUrl(QuoteModel $quoteModel)
    {
        return $this->getActionUrl($quoteModel, 'approve');
    }

    /**
     * @param   QuoteModel $quoteModel
     *
     * @return  string
     */
    public function getAuthorizeUrl(QuoteModel $quoteModel)
    {
        return $this->getActionUrl($quoteModel, 'authorize');
    }

    /**
     * @param   QuoteModel $quoteModel
     *
     * @return  string
     */
    public function getRejectUrl(QuoteModel $quoteModel)
    {
        return $this->getActionUrl($quoteModel, 'reject');
    }

    /**
     * @param QuoteModel $quoteModel
     * @param string $action
     *
     * @return string
     */
    private function getActionUrl(QuoteModel $quoteModel, $action)
    {
        $url = $this->getUrl('subaccounts/order_approve/waiting');
        $uenc = base64_encode($url);

        return $this->getUrl(
            'subaccounts/order/' . $action,
            [
                'id' => $quoteModel->getId(),
                'hash' => $quoteModel->getApproveHash(),
                'uenc' => $uenc,
            ]
        );
    }

    /**
     * @param   float $price
     * @param   string $storeId
     *
     * @return  float|string
     */
    public function getPriceHtml($price, $storeId)
    {
        return $this->pricingHelperData
            ->currencyByStore($price, $storeId, true, false);
    }

    /**
     * Get all cart items.
     *
     * @param   int $quoteId
     *
     * @return  array
     */
    public function getQuoteItems($quoteId)
    {
        /** @var \Magento\Quote\Model\Quote $quoteModel */
        $quoteModel = $this->quoteFactory
            ->create()
            ->loadByIdWithoutStore($quoteId);

        return $quoteModel->getAllVisibleItems();
    }

    /**
     * @return bool
     */
    public function isParentLoggedIn()
    {
        return !$this->viewHelper->isSubaccountLoggedIn(true);
    }
}
