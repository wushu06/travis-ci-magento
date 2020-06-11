<?php

namespace Elementary\EmployeesManager\Block\Adminhtml\Customeremployee\Edit;

use Magento\Backend\Block\Widget;

class Tabs extends Widget\Tabs
{
    /**
     * Tabs Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('customeremployee_tabs');
        $this->setDestElementId('employee_form');
        $this->setTitle(__('Employee Information'));
    }

    /**
     * Prepare Tabs
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab('main_section', [
            'label'   => __('Employee Information'),
            'content' => $this->getLayout()->createBlock(Tabs\Main::class)->toHtml()
        ]);
        $employeeId = $this->getRequest()->getParam('entity_id');
        if($employeeId) {
            $this->addTab('orders', [
                'label' => __('Employee Order'),
                'content' => $this->getLayout()->createBlock(Tabs\Orders::class)->toHtml()
            ]);
        }

        parent::_prepareLayout();

        return $this;
    }
}
