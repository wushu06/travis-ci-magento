<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Ui\Component\Listing\DataProvider;

class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    protected $_idFieldName = 'entity_id';

    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }
}