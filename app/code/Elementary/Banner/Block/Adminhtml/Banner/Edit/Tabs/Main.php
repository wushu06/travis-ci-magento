<?php

namespace Elementary\Banner\Block\Adminhtml\Banner\Edit\Tabs;

use Elementary\Banner\Model;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

/**
 * Banner Main Tab
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Main extends Generic implements TabInterface
{
    /**
     * Main constructor
     *
     * @param Context     $context
     * @param Registry    $registry
     * @param FormFactory $formFactory
     * @param array       $data
     */
    public function __construct(
        Context     $context,
        Registry    $registry,
        FormFactory $formFactory,
        array       $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Banner Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Banner Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare Banner Form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var Model\Banner $formData */
        $formData = $this->_coreRegistry->registry('banner');
        if (!$formData) {
            $formData = new DataObject();
            $formData->setData(Model\Banner::STATUS, 1);
        }
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('banner_');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Banner Information')
        ]);

        $fieldset->addField(Model\Banner::IDENTIFIER, 'text', [
            'name'     => Model\Banner::IDENTIFIER,
            'label'    => __('Identifier'),
            'title'    => __('Identifier'),
            'required' => true,
        ]);

        $fieldset->addField(Model\Banner::STATUS, 'select', [
            'name'     => Model\Banner::STATUS,
            'label'    => __('Status'),
            'title'    => __('Status'),
            'required' => true,
            'options'  => [
                '' =>  __('Please select a status...'),
                '1' => __('Enabled'),
                '0' => __('Disabled')
            ]
        ]);

        if ($formData->getData(Model\Banner::BANNER_ID)) {
            $fieldset->addField(Model\Banner::BANNER_ID, 'hidden', [
                'name' => Model\Banner::BANNER_ID
            ]);
        }

        $form->setValues($formData->getData());
        $this->setForm($form);

        parent::_prepareForm();

        return $this;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId Admin Resource
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
