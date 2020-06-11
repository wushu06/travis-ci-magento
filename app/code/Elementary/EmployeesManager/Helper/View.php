<?php


namespace Elementary\EmployeesManager\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class View
 *
 * @package Elementary\EmployeesManager\Helper
 */
class View extends AbstractHelper
{
    /**
     * @var SessionFactory
     */
    private $sessionFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryCollection;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SessionFactory $sessionFactory,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        ConfigFactory  $configFactory
    ) {
        parent::__construct($context);
        $this->sessionFactory = $sessionFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->groupRepository = $groupRepository;
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Check if customer can view technical drawings
     *
     * @return bool
     */
    public function canViewEmployeeManager()
    {

        $customerId = $this->getCustomerId();
        if (!$customerId) {
            header("Location: ".$this->storeManager->getStore()->getBaseUrl().'customer/account/login');
            die();
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            return false;
        }
        return true;

    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getSession()->getCustomerId();
    }


    /**
     * @return int
     */
    public function getGroupId()
    {
        if($this->getCustomerId()) {
            return $this->getSession()->getCustomer()->getGroupId();
        }

    }

    /**
     *
     * @param $groupId | null
     * @return string
     */
    public function getGroupNameById($groupId = null)
    {
        $groupId = $groupId == null ? $this->getGroupId() : $groupId;
        $group = $this->groupRepository->getById($groupId);
        return $group->getCode();
    }

    /**
     * Get Customer Session
     *
     * @return Session
     */
    public function getSession()
    {
        /** @var Session $session */
        $session = $this->sessionFactory->create();

        return $session;
    }
    /**
     * is customer logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->getSession()->isLoggedIn();
    }

    /**
     * Get Current Customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        /** @var Session $session */
        $session = $this->getSession();

        return $session->getCustomer();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoyByGroupId()
    {
        return $categories = $this->categoryCollection->create()
            ->addAttributeToSelect('*');
   }


}

