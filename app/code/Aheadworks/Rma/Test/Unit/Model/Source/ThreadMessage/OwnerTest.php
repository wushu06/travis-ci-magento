<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\ThreadMessage;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;

/**
 * Class OwnerTest
 * Test for \Aheadworks\Rma\Model\Source\ThreadMessage\Owner
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\ThreadMessage
 */
class OwnerTest extends TestCase
{
    /**
     * @var Owner
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
            Owner::class,
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
