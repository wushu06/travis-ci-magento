<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Serialize;

/**
 * Interface SerializerInterface
 * @package Aheadworks\Rma\Model\Serialize
 */
interface SerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param mixed $data
     * @return string|bool
     * @throws \InvalidArgumentException
     */
    public function serialize($data);

    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function unserialize($string);
}
