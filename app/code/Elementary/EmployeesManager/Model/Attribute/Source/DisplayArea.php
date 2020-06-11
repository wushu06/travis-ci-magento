<?php


namespace Elementary\EmployeesManager\Model\Attribute\Source;

/**
 * Class DisplayArea
 *
 * @package Elementary\EmployeesManager\Model\Attribute\Source
 */
class DisplayArea extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    private $collection;

    public function __construct(
        \Elementary\EmployeesManager\Model\CustomerEmployeeFactory  $collection
    ) {
        $this->collection = $collection;
    }
    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
        ['value' => '', 'label' => __('--Please Select--')],
        ['value' => 'male', 'label' => __('male')],
        ['value' => 'female', 'label' => __('female')]
        ];
        return $this->_options;
    }
    public function toOptionArray()
    {

        $collection = $this->collection->create();
        $itemArray = array('value' => '', 'label' => '--Please Select--');
        //
        $categoryArray = array();
        $categoryArray[] = $itemArray;
        foreach ($collection as $customer)
        {
            $categoryArray[] = array('value' => $customer->getId(), 'label' => $customer->getDisplayArea());

        }
        return $categoryArray;
    }
}

