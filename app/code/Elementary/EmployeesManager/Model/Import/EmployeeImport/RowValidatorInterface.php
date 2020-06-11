<?php
/**
 * Elementary Digital.
 *
 *
 * @package    Elementary StoreFinder
 * @author     nour@elementarydigital.co.uk
 * @copyright  Copyright (c) 2020 WeAreMagneto
 */
namespace Elementary\EmployeesManager\Model\Import\EmployeeImport;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
    const ERROR_INVALID_TITLE = 'InvalidValueTITLE';
    const ERROR_ID_IS_EMPTY = 'Empty';

    /**
     * Initialize validator
     *
     * @return $this
     */
    public function init($context);
}
