<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Request\PrintLabel\Address\Form;

use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMetaProvider;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Mapper;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\Modifier;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Form\AttributeMeta\AvailabilityChecker;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeMetaProviderTest
 * Test for \Aheadworks\Rma\Model\Request\PrintLabel\Address\Form
 *
 * @package Aheadworks\Rma\Test\Unit\Model\Request\PrintLabel\Address\Form
 */
class AttributeMetaProviderTest extends TestCase
{
    /**
     * @var AttributeMetaProvider
     */
    private $metaProvider;

    /**
     * @var AddressMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMetadataMock;

    /**
     * @var AvailabilityChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $availabilityCheckerMock;

    /**
     * @var Mapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapperMock;

    /**
     * @var Modifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modifierMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->addressMetadataMock = $this->getMockForAbstractClass(AddressMetadataInterface::class);
        $this->availabilityCheckerMock = $this->getMockBuilder(AvailabilityChecker::class)
            ->setMethods(['isAvailableOnForm'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapperMock = $this->getMockBuilder(Mapper::class)
            ->setMethods(['map'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->modifierMock = $this->getMockBuilder(Modifier::class)
            ->setMethods(['modify'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->metaProvider = $objectManager->getObject(
            AttributeMetaProvider::class,
            [
                'addressMetadata' => $this->addressMetadataMock,
                'availabilityChecker' => $this->availabilityCheckerMock,
                'mapper' => $this->mapperMock,
                'modifier' => $this->modifierMock
            ]
        );
    }

    /**
     * Test getMetadata method
     */
    public function testGetMetadata()
    {
        $attributeCode = 'firtstname';
        $metadata = ['label' => 'First Name', 'visible' => '1'];
        $modifiedMetadata = ['label' => 'First Name', 'visible' => '0'];

        $attributeMetadataMock = $this->getMockForAbstractClass(AttributeMetadataInterface::class);

        $this->addressMetadataMock->expects($this->once())
            ->method('getAttributes')
            ->with('customer_address_edit')
            ->willReturn([$attributeMetadataMock]);
        $this->availabilityCheckerMock->expects($this->once())
            ->method('isAvailableOnForm')
            ->with($attributeMetadataMock)
            ->willReturn(true);
        $attributeMetadataMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->mapperMock->expects($this->once())
            ->method('map')
            ->with($attributeMetadataMock)
            ->willReturn($metadata);
        $this->modifierMock->expects($this->once())
            ->method('modify')
            ->with($attributeCode, $metadata)
            ->willReturn($modifiedMetadata);

        $this->assertEquals([$attributeCode => $modifiedMetadata], $this->metaProvider->getMetadata());
    }

    /**
     * Test getMetadata method not available on form
     */
    public function testGetMetadataNotAvailableOnForm()
    {
        $attributeMetadataMock = $this->getMockForAbstractClass(AttributeMetadataInterface::class);

        $this->addressMetadataMock->expects($this->once())
            ->method('getAttributes')
            ->with('customer_address_edit')
            ->willReturn([$attributeMetadataMock]);
        $this->availabilityCheckerMock->expects($this->once())
            ->method('isAvailableOnForm')
            ->with($attributeMetadataMock)
            ->willReturn(false);

        $this->assertEquals([], $this->metaProvider->getMetadata());
    }
}
