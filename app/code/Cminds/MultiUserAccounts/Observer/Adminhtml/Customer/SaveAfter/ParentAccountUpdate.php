<?php

namespace Cminds\MultiUserAccounts\Observer\Adminhtml\Customer\SaveAfter;

use Cminds\MultiUserAccounts\Model\Config as ModuleConfig;
use Cminds\MultiUserAccounts\Model\SubaccountFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Cminds\MultiUserAccounts\Model\Service\Convert\Customer\ParentAccount as ParentAccountConverter;
use Cminds\MultiUserAccounts\Model\ResourceModel\Subaccount\CollectionFactory as SubaccountCollectionFactory;
use Cminds\MultiUserAccounts\Model\Service\Assign\Account;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Cminds\MultiUserAccounts\Api\ParentaccountInterface;

/**
 * Cminds MultiUserAccounts after customer section configuration save observer.
 * Will be executed on "customer_save_after_subaccount_update"
 * event in admin area.
 *
 * @category Cminds
 * @package  Cminds_MultiUserAccounts
 * @author   Piotr Pierzak <piotr@cminds.com>
 */
class ParentAccountUpdate implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     *
     */
    private $customerRepositoryInterface;

    /**
     * Module Config.
     *
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * Subaccount Factory.
     *
     * @var SubaccountFactory
     */
    private $subaccountFactory;

    /**
     *
     * @var SubaccountCollectionFactory
     */
    private $subaccountCollectionFactory;

    /**
     * Request object.
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * Registry Object.
     *
     * @var Registry
     */
    private $registry;

    /**
     * Parent Account Converter.
     *
     * @var ParentAccountConverter
     */
    private $parentAccountConverter;

    /**
     * Account Assigner.
     *
     * @var Account
     */
    private $accountAssigner;

    /**
     * Message Manager.
     *
     * @var ManagerInterface
     */
    private $messageManager;

    private $parentAccountInterface;

    /**
     * ParentAccountUpdate constructor.
     *
     * @param ModuleConfig $moduleConfig
     * @param SubaccountFactory $subaccountFactory
     * @param SubaccountCollectionFactory $subaccountCollectionFactory
     * @param RequestInterface $requestInterface
     * @param Registry $registry
     * @param ParentAccountConverter $parentAccountConverter
     * @param Account $accountAssigner
     * @param ManagerInterface $messageManager
     * @param ParentaccountInterface $parentAccountInterface
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        ModuleConfig $moduleConfig,
        SubaccountFactory $subaccountFactory,
        SubaccountCollectionFactory $subaccountCollectionFactory,
        RequestInterface $requestInterface,
        Registry $registry,
        ParentAccountConverter $parentAccountConverter,
        Account $accountAssigner,
        ManagerInterface $messageManager,
        ParentaccountInterface $parentAccountInterface
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->moduleConfig = $moduleConfig;
        $this->subaccountFactory = $subaccountFactory;
        $this->subaccountCollectionFactory = $subaccountCollectionFactory;
        $this->request = $requestInterface;
        $this->registry = $registry;
        $this->parentAccountConverter = $parentAccountConverter;
        $this->accountAssigner = $accountAssigner;
        $this->messageManager = $messageManager;
        $this->parentAccountInterface = $parentAccountInterface;
    }

    /**
     * Save parent customer id for the user after save on admin side.
     *
     * @param Observer $observer
     *
     * @return ParentAccountUpdate
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->moduleConfig->isEnabled() === false) {
                return $this;
            }

            $customerData = $this->getCustomerPostData();
            if (!$customerData) {
                return $this;
            }
            
            if (!empty($customerData['group_id'])) {
                $collection = $this->subaccountCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('parent_customer_id', $observer->getCustomer()->getId())
                        ->setOrder('created_at', 'desc');

                if (!empty($collection)) {
                    foreach ($collection as $subaccount) {
                        $customer = $this->customerRepositoryInterface->getById($subaccount->getCustomerId());
                        $customer->setGroupId($customerData['group_id']);
                        $this->customerRepositoryInterface->save($customer);
                    }
                }
            }
            
            // check if it's the firts time this function should be executed
            if (!$this->request->getParam('manageParentAccountIdDataFired')) {
                $this->manageParentAccountIdData();
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving parent account'));
        }
    }

    /**
     * Manage parent account id data.
     *
     * @return $this
     */
    protected function manageParentAccountIdData()
    {
        $entityId = $this->getCustomerId();
        if (!$entityId) {
            return $this;
        }

        // set a flag, that this function is fired for the first time
        $this->request->setParam('manageParentAccountIdDataFired', 1);

        $parentAccountId = $this->retrieveParentAccountId();
        if (!$parentAccountId) {
            /** If there is no parent account id, then make sure, that the customer is master account. */
            $this->parentAccountConverter->convertCustomer($entityId);
        } else {
            /** If the parent account id exists, then make sure, that the customer is sub account of that parent. */
            $this->accountAssigner->assignCustomerToParent($parentAccountId, $entityId);
        }
    }

    /**
     * Get customer id from the post.
     *
     * @return int|null
     */
    protected function getCustomerId()
    {
        $customerData = $this->getCustomerPostData();

        return isset($customerData['entity_id']) ? (int)$customerData['entity_id'] : null;
    }

    /**
     * Get customer post data.
     *
     * @return array
     */
    protected function getCustomerPostData()
    {
        return $this->request->getParam('customer') ?: [];
    }

    /**
     * Retrieve parent account id, which was sent by post.
     *
     * @return int|null
     */
    protected function retrieveParentAccountId()
    {
        $customerData = $this->getCustomerPostData();
        if (isset($customerData['parent_account_id'])) {
            if ($this->moduleConfig->showAsText()) {
                $customerEmail = trim($customerData['parent_account_id']);
                if (!empty($customerEmail)) {
                    if ($customerData['email'] !== $customerEmail) {
                        $parentAccount = $this->parentAccountInterface->getByEmail($customerEmail);
                        if ($parentAccount && $parentAccount->getId()) {
                            return (int)$parentAccount->getId();
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__('Parent email can not be the same as subaccount email.'));
                    }
                }
                return null;
            } else {
                return (int)$customerData['parent_account_id'];
            }
        }
        return null;
    }
}
