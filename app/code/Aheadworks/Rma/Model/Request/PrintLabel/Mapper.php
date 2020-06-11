<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestPrintLabelInterface;
use Magento\Framework\EntityManager\MapperInterface;
use Aheadworks\Rma\Model\Serialize\Factory;
use Aheadworks\Rma\Model\Serialize\SerializerInterface;

/**
 * Class Mapper
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel
 */
class Mapper implements MapperInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Factory $factory
     */
    public function __construct(
        Factory $factory
    ) {
        $this->serializer = $factory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function entityToDatabase($entityType, $data)
    {
        if (is_array($data[RequestInterface::PRINT_LABEL])) {
            $data[RequestInterface::PRINT_LABEL] = $this->serializer->serialize($data[RequestInterface::PRINT_LABEL]);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function databaseToEntity($entityType, $data)
    {
        if (empty($data[RequestInterface::PRINT_LABEL])) {
            return $data;
        }
        $data[RequestInterface::PRINT_LABEL] = $this->serializer->unserialize($data[RequestInterface::PRINT_LABEL]);
        $street = $data[RequestInterface::PRINT_LABEL][RequestPrintLabelInterface::STREET];
        if (!is_array($street)) {
            $data[RequestInterface::PRINT_LABEL][RequestPrintLabelInterface::STREET] = [$street, ''];
        }
        return $data;
    }
}
