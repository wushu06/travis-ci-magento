<?php
namespace Elementary\EmployeesOrders\Model\Search;

use Magento\Framework\DataObject;
use Magento\Backend\Helper\Data;
use Elementary\EmployeesOrders\Model\ResourceModel\EmployeeOrder\CollectionFactory;

/**
 * @api
 */
class EmployeeOrder extends DataObject
{
    /**
     * Adminhtml data
     *
     * @var Data
     */
    protected $adminhtmlData = null;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Data $adminhtmlData
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $adminhtmlData
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->adminhtmlData = $adminhtmlData;
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $query = $this->getQuery();
        $collection = $this->collectionFactory->create()->addFieldToFilter(
            'name',
            ['like' => '%' . $query . '%']
        )->setCurPage(
            $this->getStart()
        )->setPageSize(
            $this->getLimit()
        )->load();

        foreach ($collection as $item) {
            $result[] = [
                'id' => 'employee_order' . $item->getId(),
                'type' => __('Employee Order'),
                'name' => __('Employee Order %1', $item->getName()),
                'description' => __('Employee Order %1', $item->getName()),
                'url' => $this->adminhtmlData->getUrl(
                    'elementary_employees_manager/employee_order/edit',
                    ['employee_order_id' => $item->getId()]
                ),
            ];
        }

        $this->setResults($result);

        return $this;
    }
}
