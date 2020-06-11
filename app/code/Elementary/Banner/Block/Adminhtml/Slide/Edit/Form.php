<?php

namespace Elementary\Banner\Block\Adminhtml\Slide\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Slide Form
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
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
                'id'      => 'edit_form',
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
