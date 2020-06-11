<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Request\PrintLabel\Address\Form\AttributeMeta;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierInterface;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeMetaProviderTest
 * Test for \Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Request\PrintLabel\Address\Form\AttributeMeta
 */
class ModifierTest extends TestCase
{
    /**
     * @var Modifier
     */
    private $modifier;

    /**
     * @var ModifierPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modifierPoolMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->modifierPoolMock = $this->getMockBuilder(ModifierPool::class)
            ->setMethods(['getModifier'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->modifier = $objectManager->getObject(
            Modifier::class,
            ['modifierPool' => $this->modifierPoolMock]
        );
    }

    /**
     * Test modify method
     */
    public function testModify()
    {
        $attributeCode = 'firstname';
        $metadata = ['label' => 'First Name'];
        $modifiedMetadata = ['label' => 'First Name changed'];

        $modifierMock = $this->getMockForAbstractClass(ModifierInterface::class);

        $this->modifierPoolMock->expects($this->once())
            ->method('getModifier')
            ->with($attributeCode)
            ->willReturn($modifierMock);
        $modifierMock->expects($this->once())
            ->method('modify')
            ->with($metadata)
            ->willReturn($modifiedMetadata);

        $this->assertEquals(
            $modifiedMetadata,
            $this->modifier->modify($attributeCode, $metadata)
        );
    }
}
