<?php

namespace Elementary\EmployeesManager\Block\Adminhtml\Customeremployee\Edit\Tabs;

use Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;


class Main extends Generic implements TabInterface
{
    /**
     * @var CustomerEmployeeRepositoryInterface
     */
    private $employeeRepository;
    /**
     * @var \Elementary\EmployeesManager\Model\Attribute\Source\Groups
     */
    private $groups;
    /**
     * @var \Elementary\EmployeesManager\Model\Attribute\Source\DisplayArea
     */
    private $displayArea;

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
        CustomerEmployeeRepositoryInterface $employeeRepository,
        \Elementary\EmployeesManager\Model\Attribute\Source\Groups $groups,
        \Elementary\EmployeesManager\Model\Attribute\Source\DisplayArea $displayArea,
        array       $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->employeeRepository = $employeeRepository;
        $this->groups = $groups;
        $this->displayArea = $displayArea;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Employee Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Employee Information');
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
     * Prepare EmployeesManager Form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $employeeId = $this->getRequest()->getParam('entity_id');
        $model = $this->_coreRegistry->registry('customeremployee');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' =>
                [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('/customeremployee/save/'),
                    'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('customeremployee_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Add Employee'), 'class' => 'fieldset-wide']
        );
        if ($model && $model->getId()) {
            $fieldset->addField(
                'entity_id',
                'hidden',
                ['name' => 'entity_id', 'value' =>  $employeeId]
            );
        }
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'label' => __('Comment'),
                'title' => __('Comment'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'printed_name',
            'text',
            [
                'name' => 'printed_name',
                'label' => __('Printed name'),
                'title' => __('Printed name'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'group_id',
            'select',
            array('name' => 'group_id',
                'label' => __('Group'),
                'title' => __('Group'), 'required' => false,
                'values' =>$this->groups->toOptionArray()));
        $fieldset->addField(
            'display_area',
            'select',
            array('name' => 'display_area',
                'label' => __('Display area'),
                'title' => __('Display area'), 'required' => false,
                'values' =>$this->displayArea->getAllOptions()));

        if ($model && $model->getId()) {
            $data = $model->getData();
            $form->setValues($data);
        }
        $this->setForm($form);
        $form->setUseContainer(true);
        return parent::_prepareForm();
    }

}
