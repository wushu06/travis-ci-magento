<?php
namespace Elementary\EmployeesManager\Controller\CustomerEmployee;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface;
use Elementary\EmployeesManager\Model\CustomerEmployee\Url as UrlModel;

class View extends Action
{
    /**
     * @var string
     */
    const BREADCRUMBS_CONFIG_PATH = 'elementary_employees_manager/customer_employee/breadcrumbs';
    /**
     * @var \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface
     */
    protected $customerEmployeeRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Elementary\EmployeesManager\Model\CustomerEmployee\Url
     */
    protected $urlModel;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param CustomerEmployeeRepositoryInterface $customerEmployeeRepository
     * @param Registry $coreRegistry
     * @param UrlModel $urlModel
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        CustomerEmployeeRepositoryInterface $customerEmployeeRepository,
        Registry $coreRegistry,
        UrlModel $urlModel,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerEmployeeRepository = $customerEmployeeRepository;
        $this->coreRegistry = $coreRegistry;
        $this->urlModel = $urlModel;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $entityId = (int)$this->getRequest()->getParam('id');
            $model = $this->_objectManager->create(\Elementary\EmployeesManager\Model\CustomerEmployee::class);
            $collection = $model->getCollection()->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id',['eq'=> $entityId]);
            $customerEmployee =$collection->toArray();

        } catch (\Exception $e) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        // $this->coreRegistry->register('current_customer_employee', $customerEmployee);
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set($customerEmployee[$entityId]['name']);
        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle && $pageMainTitle instanceof \Magento\Theme\Block\Html\Title) {
            $pageMainTitle->setPageTitle($customerEmployee[$entityId]['name']);
        }
        if ($this->scopeConfig->isSetFlag(self::BREADCRUMBS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)) {
            /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
            $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'link'  => $this->_url->getUrl('')
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'CustomerEmployees',
                    [
                        'label' => __('Customer Employees'),
                        'link'  => $this->urlModel->getListUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'customerEmployee-' . $customerEmployee[$entityId]['name'],
                    [
                        'label' => $customerEmployee[$entityId]['name']
                    ]
                );
            }
        }
        return $resultPage;
    }
}
