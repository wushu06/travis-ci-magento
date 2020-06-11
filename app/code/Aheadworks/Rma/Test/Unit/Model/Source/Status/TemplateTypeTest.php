<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\Status;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\Status\TemplateType;

/**
 * Class TemplateTypeTest
 * Test for \Aheadworks\Rma\Model\Source\Status\TemplateType
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\Status
 */
class TemplateTypeTest extends TestCase
{
    /**
     * @var TemplateType
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
            TemplateType::class,
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
