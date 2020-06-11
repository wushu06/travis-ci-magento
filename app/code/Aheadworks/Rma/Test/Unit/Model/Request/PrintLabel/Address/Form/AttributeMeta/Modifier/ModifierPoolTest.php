<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierInterface;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ModifierPoolTest
 * Test for \Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier\ModifierPool
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier
 */
class ModifierPoolTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
    }

    /**
     * Test getModifier method
     */
    public function testGetModifier()
    {
        $attributeCode = 'firstname';
        $modifierClassName = 'FirstNameModifier';

        /** @var ModifierPool $modifierPool */
        $modifierPool = $this->objectManager->getObject(
            ModifierPool::class,
            ['objectManager' => $this->objectManagerMock]
        );
        $modifierMock = $this->getMockForAbstractClass(ModifierInterface::class);

        $class = new \ReflectionClass($modifierPool);

        $modifiersProperty = $class->getProperty('modifiers');
        $modifiersProperty->setAccessible(true);
        $modifiersProperty->setValue($modifierPool, [$attributeCode => $modifierClassName]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($modifierClassName)
            ->willReturn($modifierMock);

        $this->assertSame($modifierMock, $modifierPool->getModifier($attributeCode));
    }

    /**
     * Test getModifier method
     */
    public function testGetModifierCustom()
    {
        $attributeCode = 'firstname';
        $modifierClassName = 'FirstNameCustomModifier';

        /** @var ModifierPool $modifierPool */
        $modifierPool = $this->objectManager->getObject(
            ModifierPool::class,
            [
                'objectManager' => $this->objectManagerMock,
                'modifiers' => [$attributeCode => $modifierClassName]
            ]
        );
        $modifierMock = $this->getMockForAbstractClass(ModifierInterface::class);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($modifierClassName)
            ->willReturn($modifierMock);

        $this->assertSame($modifierMock, $modifierPool->getModifier($attributeCode));
    }
}
