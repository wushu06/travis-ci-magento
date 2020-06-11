<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\CustomField;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\CustomField\StatusType;

/**
 * Class StatusTypeTest
 * Test for \Aheadworks\Rma\Model\Source\CustomField\StatusType
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\CustomField
 */
class StatusTypeTest extends TestCase
{
    /**
     * @var StatusType
     */
    private $model;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            StatusType::class,
            []
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
