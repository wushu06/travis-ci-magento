<?php
namespace Elementary\EmployeesOrders\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface;
use Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterfaceFactory;
use Elementary\EmployeesOrders\Api\Data\EmployeeOrderSearchResultInterfaceFactory;
use Elementary\EmployeesOrders\Api\EmployeeOrderRepositoryInterface;
use Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder as EmployeeOrderResourceModel;
use Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder\Collection;
use Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder\CollectionFactory as EmployeeOrderCollectionFactory;

class EmployeeOrderRepository implements EmployeeOrderRepositoryInterface
{
    /**
     * Cached instances
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Employee Order resource model
     *
     * @var EmployeeOrderResourceModel
     */
    protected $resource;

    /**
     * Employee Order collection factory
     *
     * @var EmployeeOrderCollectionFactory
     */
    protected $employeeOrderCollectionFactory;

    /**
     * Employee Order interface factory
     *
     * @var EmployeeOrderInterfaceFactory
     */
    protected $employeeOrderInterfaceFactory;

    /**
     * Data Object Helper
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     *
     * @var EmployeeOrderSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     * @param EmployeeOrderResourceModel $resource
     * @param EmployeeOrderCollectionFactory $employeeOrderCollectionFactory
     * @param EmployeeOrdernterfaceFactory $employeeOrderInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param EmployeeOrderSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        EmployeeOrderResourceModel $resource,
        EmployeeOrderCollectionFactory $employeeOrderCollectionFactory,
        EmployeeOrderInterfaceFactory $employeeOrderInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        EmployeeOrderSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resource             = $resource;
        $this->employeeOrderCollectionFactory = $employeeOrderCollectionFactory;
        $this->employeeOrderInterfaceFactory  = $employeeOrderInterfaceFactory;
        $this->dataObjectHelper     = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Save Employee Order.
     *
     * @param \Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface $employeeOrder
     * @return \Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(EmployeeOrderInterface $employeeOrder)
    {
        /** @var EmployeeOrderInterface|\Magento\Framework\Model\AbstractModel $employeeOrder */
        try {
            $this->resource->save($employeeOrder);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Employee Order: %1',
                $exception->getMessage()
            ));
        }
        return $employeeOrder;
    }

    /**
     * Retrieve Employee Order
     *
     * @param int $employeeOrderId
     * @return \Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($employeeOrderId)
    {
        if (!isset($this->instances[$employeeOrderId])) {
            /** @var EmployeeOrderInterface|\Magento\Framework\Model\AbstractModel $employeeOrder */
            $employeeOrder = $this->employeeOrderInterfaceFactory->create();
            $this->resource->load($employeeOrder, $employeeOrderId);
            if (!$employeeOrder->getId()) {
                throw new NoSuchEntityException(__('Requested Employee Order doesn\'t exist'));
            }
            $this->instances[$employeeOrderId] = $employeeOrder;
        }
        return $this->instances[$employeeOrderId];
    }

    /**
     * Retrieve Employees Orders matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Elementary\EmployeesOrders\Api\Data\EmployeeOrderSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Elementary\EmployeesOrders\Api\Data\EmployeeOrderSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder\Collection $collection */
        $collection = $this->employeeOrderCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? SortOrder::SORT_ASC : SortOrder::SORT_DESC
                );
            }
        } else {
            $collection->addOrder('main_table.' . EmployeeOrderInterface::EMPLOYEE_ORDER_ID, SortOrder::SORT_ASC);
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var EmployeeOrderInterface[] $employeesOrders */
        $employeesOrders = [];
        /** @var \Elementary\EmployeesOrders\Model\EmployeeOrder $employeeOrder */
        foreach ($collection as $employeeOrder) {
            /** @var EmployeeOrderInterface $employeeOrderDataObject */
            $employeeOrderDataObject = $this->employeeOrderInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $employeeOrderDataObject,
                $employeeOrder->getData(),
                EmployeeOrderInterface::class
            );
            $employeesOrders[] = $employeeOrderDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($employeesOrders);
    }

    /**
     * Delete Employee Order
     *
     * @param EmployeeOrderInterface $employeeOrder
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(EmployeeOrderInterface $employeeOrder)
    {
        /** @var EmployeeOrderInterface|\Magento\Framework\Model\AbstractModel $employeeOrder */
        $id = $employeeOrder->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($employeeOrder);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to removeEmployee Order %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Employee Order by ID.
     *
     * @param int $employeeOrderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($employeeOrderId)
    {
        $employeeOrder = $this->get($employeeOrderId);
        return $this->delete($employeeOrder);
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
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }

    /**
     * clear caches instances
     * @return void
     */
    public function clear()
    {
        $this->instances = [];
    }
    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->employeeOrderCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->employeeOrderCollectionFactory->create();
    }
}
