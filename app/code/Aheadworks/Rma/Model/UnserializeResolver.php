<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model;

use Aheadworks\Rma\Model\Serialize\Factory;

/**
 * Class UnserializeResolver
 *
 * @package Aheadworks\Rma\Model
 */
class UnserializeResolver
{
    /**
     * @var Factory;
     */
    private $factory;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     */
    public function unserialize($string)
    {
        $result = $this->unserializeString($string);
        return $result === false ? $this->jsonDecodeString($string) : $result;
    }

    /**
     * Unserialize string with unserialize method
     *
     * @param $string
     * @return array|bool
     */
    private function unserializeString($string)
    {
        $result = @unserialize($string);

        if ($result !== false || $string === 'b:0;') {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Unserialize string with json_decode method
     *
     * @param $string
     * @return array
     */
    private function jsonDecodeString($string)
    {
        $serializer = $this->factory->create();
        return $serializer->unserialize($string);
    }
}
