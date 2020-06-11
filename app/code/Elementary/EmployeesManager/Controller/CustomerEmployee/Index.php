<?php
namespace Elementary\EmployeesManager\Controller\CustomerEmployee;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Index extends Action
{
    /**
     * @var string
     */
    const BREADCRUMBS_CONFIG_PATH = 'elementary_employees_manager/customer_employee/breadcrumbs';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $resultPageFactory;
    /**
     * @var \Elementary\EmployeesManager\Api\Data\CustomerEmployeeSearchResultsInterface
     */
    private $customerEmployeeSearchResults;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface $customerEmployeeSearchResults,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->customerEmployeeSearchResults = $customerEmployeeSearchResults;
    }
    /**
     * @return mixed|void
     */
    protected function _initModel()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create(\Elementary\EmployeesManager\Model\CustomerEmployee::class)->load($id);
        if (!$model->getId() && $id) {
            $this->messageManager->addErrorMessage('This employee no longer exists.');
            return;
        }
        return $model;
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $resultPage = $this->resultPageFactory->create();
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
                    ]
                );
            }
        }
        return $resultPage;
    }
}
