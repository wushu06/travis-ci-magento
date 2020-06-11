<?php

namespace Elementary\EmployeesManager\Block\Adminhtml\Customeremployee\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * Prepare Form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id'      => 'employee_form',
                'action'  => $this->getData('action'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            ]
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        parent::_prepareForm();

        return $this;
    }
}
