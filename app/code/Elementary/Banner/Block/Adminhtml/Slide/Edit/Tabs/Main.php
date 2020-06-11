<?php

namespace Elementary\Banner\Block\Adminhtml\Slide\Edit\Tabs;

use Elementary\Banner\Model\Slide;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

/**
 * Slide Main Tab
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Main extends Generic implements TabInterface
{
    /**
     * Wysiwyg Config
     *
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * Group Collection Factory
     *
     * @var CollectionFactory
     */
    protected $_groupCollectionFactory;

    /**
     * Main constructor
     *
     * @param Context           $context
     * @param Registry          $registry
     * @param FormFactory       $formFactory
     * @param Config            $wysiwygConfig
     * @param CollectionFactory $groupCollectionFactory
     * @param array             $data
     */
    public function __construct(
        Context           $context,
        Registry          $registry,
        FormFactory       $formFactory,
        Config            $wysiwygConfig,
        CollectionFactory $groupCollectionFactory,
        array             $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_groupCollectionFactory = $groupCollectionFactory;
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
        return __('Slide Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Slide Information');
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
     * Prepare Form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
        /** @var Slide $formData */
        $formData = $this->_coreRegistry->registry('slide');
        if (!$formData) {
            $formData = new DataObject();
            $formData->setData(Slide::STATUS, 1);
        }
        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('slide_');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Slide Information')
        ]);;

        $fieldset->addField(Slide::TITLE, 'text', [
            'name'     => Slide::TITLE,
            'label'    => __('Title'),
            'title'    => __('Title'),
            'required' => true,
        ]);

        $fieldset->addField(Slide::CONTENT, 'editor', [
            'name'     => Slide::CONTENT,
            'label'    => __('Content'),
            'title'    => __('Content'),
            'wysiwyg'  => true,
            'config'   => $this->_wysiwygConfig->getConfig(),
            'required' => false,
        ]);

        $fieldset->addField(Slide::SHOW_BUTTON, 'select', [
            'name'     => Slide::SHOW_BUTTON,
            'label'    => __('Show Button'),
            'title'    => __('Show Button'),
            'required' => false,
            'options'  => [
                '' =>  __('Please select if button is shown...'),
                '1' => __('Yes'),
                '0' => __('No')
            ]
        ]);

        $fieldset->addField(Slide::BUTTON_TITLE, 'text', [
            'name'     => Slide::BUTTON_TITLE,
            'label'    => __('Button Title'),
            'title'    => __('Button Title'),
            'required' => false,
        ]);

        $fieldset->addField(Slide::URL, 'text', [
            'name'     => Slide::URL,
            'label'    => __('Url'),
            'title'    => __('Url'),
            'required' => true,
        ]);

        $fieldset->addField(Slide::URL_TITLE, 'text', [
            'name'     => Slide::URL_TITLE,
            'label'    => __('Url Title'),
            'title'    => __('Url Title'),
            'required' => true,
        ]);

        $fieldset->addField(Slide::IMAGE, 'image', [
            'name'     => Slide::IMAGE,
            'label'    => __('Image'),
            'title'    => __('Image'),
            'required' => true,
        ]);

        $fieldset->addField(Slide::START_DATE, 'date', [
            'name'        => Slide::START_DATE,
            'date_format' => $dateFormat,
            'time_format' => $timeFormat,
            'label'       => __('Start Date'),
            'title'       => __('Start Date'),
            'required'    => true,
        ]);

        $fieldset->addField(Slide::FINISH_DATE, 'date', [
            'name'        => Slide::FINISH_DATE,
            'date_format' => $dateFormat,
            'time_format' => $timeFormat,
            'label'       => __('Finish Date'),
            'title'       => __('Finish Date'),
            'required'    => true,
        ]);

        $fieldset->addField(Slide::STATUS, 'select', [
            'name'     => Slide::STATUS,
            'label'    => __('Status'),
            'title'    => __('Status'),
            'required' => true,
            'options'  => [
                '' =>  __('Please select a status...'),
                '1' => __('Enabled'),
                '0' => __('Disabled')
            ]
        ]);

        $fieldset->addField('customer_group', 'multiselect', [
            'name'     => 'customer_group',
            'label'    => __('Available To Customer Groups'),
            'title'    => __('Available To Customer Groups'),
            'required' => false,
            'values'   => $this->_getCustomerGroupOptions(),
            'note'     => __('Leave blank if used in all groups.'),
        ]);

        if ($formData->getData(Slide::SLIDE_ID)) {
            $fieldset->addField(Slide::SLIDE_ID, 'hidden', [
                'name' => Slide::SLIDE_ID
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

    /**
     * Get Customer Group Options
     *
     * @return array
     */
    protected function _getCustomerGroupOptions()
    {
        /** @var Collection|Group[] $groups */
        $groups = $this->_groupCollectionFactory->create();

        return $groups->toOptionArray();
    }
}
