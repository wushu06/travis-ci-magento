<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Url;

use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class ParamEncryptor
 *
 * @package Aheadworks\Rma\Model\Url
 */
class ParamEncryptor
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var array
     */
    private $keyParamCache = [];

    /**
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Encrypt url params
     *
     * @param array $params
     * @return string
     */
    public function encrypt($params = [])
    {
        $key = '';
        $preparedParams = [];
        foreach ($params as $param => $value) {
            $preparedParams[] = $param . ':' . $value;
        }
        $key .= implode(';', $preparedParams);

        return base64_encode($this->encryptor->encrypt($key));
    }

    /**
     * Decrypt url params
     *
     * @param string $paramKey
     * @param string $key
     * @return mixed
     */
    public function decrypt($paramKey, $key)
    {
        if (!isset($this->keyParamCache[$key])) {
            $preparedParams = [];
            $stringParams = $this->encryptor->decrypt(base64_decode($key));
            $params = explode(';', $stringParams);
            foreach ($params as $param) {
                list($param, $value) = explode(':', $param);
                $preparedParams[$param] = $value;
            }
            $this->keyParamCache[$key] = $preparedParams;
        }

        return isset($this->keyParamCache[$key][$paramKey]) ? $this->keyParamCache[$key][$paramKey] : null;
    }
}
