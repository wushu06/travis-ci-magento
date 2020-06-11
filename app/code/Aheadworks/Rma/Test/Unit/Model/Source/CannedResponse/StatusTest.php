<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Source\CannedResponse;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Aheadworks\Rma\Model\Source\CannedResponse\Status as CannedResponseStatus;

/**
 * Class StatusTest
 * Test for \Aheadworks\Rma\Model\Source\CannedResponse\Status
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Source\CannedResponse
 */
class StatusTest extends TestCase
{
    /**
     * @var CannedResponseStatus
     */
    private $sourceModel;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->sourceModel = $objectManager->getObject(
            CannedResponseStatus::class,
            []
        );
    }

    /**
     * Test toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->sourceModel->toOptionArray()));
    }

    /**
     * Test toOptionArrayForMassStatus method
     */
    public function toOptionArrayForMassStatus()
    {
        $this->assertTrue(is_array($this->sourceModel->toOptionArrayForMassStatus()));
    }
}
