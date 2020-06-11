<?php


namespace Elementary\EmployeesManager\Model;

use Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface;
use Elementary\EmployeesManager\Api\Data\CustomerEmployeeSearchResultsInterfaceFactory;
use Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterfaceFactory;
use Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface;
use Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee as ResourceCustomerEmployee;
use Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee\CollectionFactory as CustomerEmployeeCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class CustomerEmployeeRepository
 *
 * @package Elementary\EmployeesManager\Model
 */
class CustomerEmployeeRepository implements CustomerEmployeeRepositoryInterface
{

    protected $resource;

    protected $customerEmployeeFactory;

    protected $customerEmployeeCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataCustomerEmployeeFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \Elementary\EmployeesManager\Helper\View
     */
    private $helper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    private $groupId;

    /**
     * @param ResourceCustomerEmployee $resource
     * @param CustomerEmployeeFactory $customerEmployeeFactory
     * @param CustomerEmployeeInterfaceFactory $dataCustomerEmployeeFactory
     * @param CustomerEmployeeCollectionFactory $customerEmployeeCollectionFactory
     * @param CustomerEmployeeSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceCustomerEmployee $resource,
        CustomerEmployeeFactory $customerEmployeeFactory,
        CustomerEmployeeInterfaceFactory $dataCustomerEmployeeFactory,
        CustomerEmployeeCollectionFactory $customerEmployeeCollectionFactory,
        CustomerEmployeeSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Elementary\EmployeesManager\Helper\View $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->resource = $resource;
        $this->customerEmployeeFactory = $customerEmployeeFactory;
        $this->customerEmployeeCollectionFactory = $customerEmployeeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCustomerEmployeeFactory = $dataCustomerEmployeeFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->request = $request;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->groupId = $this->helper->getGroupId();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->customerEmployeeFactory->create();
    }
    /**
     * {@inheritdoc}
     */
    public function save(
        \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface $customerEmployee
    ) {
        /* if (empty($customerEmployee->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $customerEmployee->setStoreId($storeId);
        } */

        $customerEmployeeData = $this->extensibleDataObjectConverter->toNestedArray(
            $customerEmployee,
            [],
            \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface::class
        );
        $customerEmployeeData['group_id'] = $this->groupId;
        $customerEmployeeModel = $this->customerEmployeeFactory->create()->setData($customerEmployeeData);
        try {
             $this->resource->save($customerEmployeeModel);
            $this->messageManager->addSuccessMessage(__(
                'Employee: %1 has been saved',
                $customerEmployee->getName()
            ));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__(
                'Could not save the Employee: %1',
                $exception->getMessage()
            ));
            throw new CouldNotSaveException(__(
                'Could not save the Employee: %1',
                $exception->getMessage()
            ));
        }

        return  $customerEmployeeModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($customerEmployeeId)
    {
        $customerEmployee = $this->customerEmployeeFactory->create();
        $this->resource->load($customerEmployee, $customerEmployeeId);
        if (!$customerEmployee->getId()) {
            throw new NoSuchEntityException(__('Employee with id "%1" does not exist.', $customerEmployeeId));
        }
        return $customerEmployee->getDataModel();
    }


    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Elementary\EmployeesManager\Api\Data\CustomerEmployeeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {

        $collection = $this->customerEmployeeCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('group_id', ['eq' => $this->groupId]);
        if($criteria->getFilterGroups() && !empty($criteria->getFilterGroups())){
            foreach ($criteria->getFilterGroups() as $group) {
                $this->addFilterGroupToCollection($group, $collection);
            }

        }
        $sortOrders = $criteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($criteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? SortOrder::SORT_ASC : SortOrder::SORT_DESC
                );
            }
        } else {
            $collection->addOrder('main_table.' . CustomerEmployeeInterface::ID, SortOrder::SORT_DESC);
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());



        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }


    /**
     * {@inheritdoc}
     */
    public function delete(
        \Elementary\EmployeesManager\Api\Data\CustomerEmployeeInterface $customerEmployee
    ) {
        try {
            $customerEmployeeModel = $this->customerEmployeeFactory->create();
            $this->resource->load($customerEmployeeModel, $customerEmployee->getEntityId());
            $this->resource->delete($customerEmployeeModel);
            $this->messageManager->addSuccessMessage(__(
                'Employee: %1 has been deleted',
                $customerEmployee->getName()
            ));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__(
                'Could not delete the Employee: %1',
                $exception->getMessage()
            ));
            throw new CouldNotDeleteException(__(
                'Could not delete the Employee: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($customerEmployeeId)
    {
        return $this->delete($this->get($customerEmployeeId));
    }
    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->dataCustomerEmployeeFactory->create();
    }
    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = '';
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {

            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields = $filter->getField();

            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }

        return $this;
    }

}

