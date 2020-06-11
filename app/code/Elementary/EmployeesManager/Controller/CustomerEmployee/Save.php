<?php

/*
 *
 *
 * This controller is not used anymore
 * it has been replaced by webapi
 * this will be deleted in next module upgrade
 *
 *
 *
 *
 */
namespace Elementary\EmployeesManager\Controller\CustomerEmployee;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Save
 * @package Elementary\EmployeesManager\Controller\CustomerEmployee
 */
class Save extends Action
{
    /** @var PageFactory $resultPageFactory */
    protected $resultPageFactory;
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;
    /**
     * @var \Elementary\EmployeesManager\Helper\View
     */
    private $helper;
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface
     */
    private $employeeRepository;


    /**
     * Result constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Elementary\EmployeesManager\Helper\View $helper,
        \Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\CollectionFactory $collectionFactory,
        \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface $employeeRepository,
        PageFactory $pageFactory
    )
    {
        $this->resultPageFactory = $pageFactory;
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * The controller action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $action = $this->getRequest()->getParam('action');
        /* @var Json $result */
        $resultJson = $this->resultJsonFactory->create();
        switch ($action) {
            case "delete":
                $this->deleteData();
                break;
            case "save":
                $this->saveData();
                break;
            case 'search':
                $result = $this->searchModel();
                return $resultJson->setData($result);
                break;
        }
        $result = $this->loadData();
        return $resultJson->setData($result);
    }

    /**
     * @param string $term
     * @return array
     */
    protected function loadData($term = '')
    {
        list($perPage, $page,  $collection) = $this->getCollectionData();
        if($term !== ''){
            $collection = $collection
                ->addAttributeToFilter('name',['regexp'=> $term]);
        }
        $collection = $collection
            ->setPageSize($perPage) // only get 10 products
            ->setCurPage($page)
            ->setOrder('entity_id', 'DESC')
            ->load();
        $pageSize = $collection->getSize();
        return array(
            'data' => $collection->toArray(),
            'total' => $pageSize
        );
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
     *
     */
    protected function saveData()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data['name'] == '' || !$group_id = $this->helper->getGroupId()) {
            $this->messageManager->addErrorMessage('Employee name can\'t be empty!');
            return;
        }
        $data['group_id'] = $group_id;
        $entityId = (int)$this->getRequest()->getParam('id');
        $model = $this->_initModel();
        if($entityId){
            $data['entity_id'] = $entityId;
            $data = array_merge($model->getData(), $data);
        }
        $model->setData($data)
            ->save();
        if(!$model){
            $this->messageManager->addErrorMessage('Employee can\'t be saved!');
        }
        $this->messageManager->addSuccessMessage('Employee has been saved!');

    }

    /**
     * @throws LocalizedException
     */
    protected function deleteData()
    {
        $model = $this->_initModel();
        if(!$model || !$model->getId()){
            $this->messageManager->addErrorMessage('Employee not found!');
            return;
        }
        $this->messageManager->addSuccessMessage('Employee has been deleted!');
        $this->employeeRepository->delete($model);


    }

    /**
     * @return array
     */
    protected function searchModel()
    {
        return $this->loadData($this->getRequest()->getParam('search'));
    }

    /**
     * @return array
     */
    protected function getCollectionData(): array
    {
        $group_id = $this->helper->getGroupId();
        $data = $this->getRequest()->getParams();
        $perPage = $data['size'];
        $page = 1;
        if ($data['pageNumber']) {
            $page = $data['pageNumber'];
        }
        $model = $this->_objectManager->create(\Elementary\EmployeesManager\Model\CustomerEmployee::class);
        $collection = $model->getCollection()->addAttributeToSelect('*')
            ->addAttributeToFilter('group_id', ['eq' => $group_id]);
        $entityId = (int)$this->getRequest()->getParam('id');
        if($entityId && $data['action'] !== 'delete'){
            $collection->addAttributeToFilter('entity_id', ['eq' => $entityId]);
        }

        return array($perPage, $page, $collection);
    }
}
