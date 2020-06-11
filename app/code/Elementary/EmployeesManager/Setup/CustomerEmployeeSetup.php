<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Setup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;

class CustomerEmployeeSetup extends EavSetup {
    /**
     * Entity type for Hello World EAV attributes
     */
    const ENTITY_TYPE_CODE = 'elementary_customeremployee';

    /**
     * EAV Entity type for Hello World EAV attributes
     */
    const EAV_ENTITY_TYPE_CODE = 'elementary_customeremployee';

    /**
     * Retrieve Entity Attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getAttributes() {
        $attributes = [];
        $attributes['name'] = [
            'type'   => 'varchar',
            'label'  => 'Name',
            'input'  => 'text',
            'group' => 'General',
        ];
        $attributes['customer_id'] = [
            'type'   => 'int',
            'label'  => 'Customer Id',
            'input'  => 'text',
            'group' => 'General',
        ];
        $attributes['group_id'] = [
            'type'   => 'int',
            'label'  => 'Group Id',
            'input'  => 'text',
            'group' => 'General',
        ];
        $attributes['comment'] = [
            'type'   => 'varchar',
            'label'  => 'Comment',
            'input'  => 'text',
            'group' => 'General',
        ];
        $attributes['status'] = [
            'type' => 'int',
            'label' => 'Status',
            'input' => 'boolean',
            'source' => '',
            'frontend' => '',
            'required' => true,
            'backend' => '',
            'default' => null,
            'user_defined' => true,
            'unique' => false,
            'group' => 'General',
        ];
        $attributes['store_id'] = [
            'type' => 'int',
            'label' => 'Status',
            'input' => 'boolean',
            'source' => '',
            'frontend' => '',
            'required' => true,
            'backend' => '',
            'default' => 0,
            'user_defined' => true,
            'unique' => false,
            'group' => 'General',
        ];
         $attributes['display_area'] = [
            'type'   => 'int',
            'label'  => 'Display Area',
            'input'  => 'select',
            'source' => \Elementary\EmployeesManager\Model\Attribute\Source\DisplayArea::class,
            'group' => 'General',
        ];
        $attributes['printed_name'] = [
            'type'   => 'varchar',
            'label'  => 'Printed name',
            'input'  => 'select',
            'group' => 'General',
        ];

        // Add your more entity attributes here...

        return $attributes;
    }

    /**
     * Retrieve default entities
     *
     * @return array
     */
    public function getDefaultEntities() {
        $entities = [
            self::ENTITY_TYPE_CODE => [
                'entity_model' => 'Elementary\EmployeesManager\Model\ResourceModel\CustomerEmployee',
                'attribute_model' => 'Elementary\EmployeesManager\Model\ResourceModel\Eav\Attribute',
                'table' => self::ENTITY_TYPE_CODE,
                'increment_model' => null,
                'additional_attribute_table' => 'elementary_customeremployee_eav_attribute',
                'entity_attribute_collection' => 'Elementary\EmployeesManager\Model\ResourceModel\Attribute\Collection',
                'attributes' => $this->getAttributes(),
            ],
        ];

        return $entities;
    }
}