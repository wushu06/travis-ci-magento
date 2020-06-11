<?php

namespace Elementary\EmployeesManager\Model\Attribute\Source;

class Groups implements \Magento\Framework\Option\ArrayInterface
{


    protected $_storeManager;
    /**
     * @var \Magento\Customer\Ui\Component\Listing\Column\Group\Options
     */
    private $collection;

    public function __construct(
        \Magento\Customer\Ui\Component\Listing\Column\Group\Options  $collection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_storeManager = $storeManager;
        $this->collection = $collection;
    }

    public function toOptionArray()
    {


        return $this->collection->toOptionArray();
    }
}


?>
