<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\CustomField;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class TypeTest
 * Test for \Aheadworks\Rma\Model\Source\CustomField\Type
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\CustomField
 */
class TypeTest extends TestCase
{
    /**
     * @var Type
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
            Type::class,
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
