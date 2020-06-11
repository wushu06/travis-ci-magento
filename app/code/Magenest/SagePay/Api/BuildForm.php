<?php


namespace Magenest\SagePay\Api;


interface BuildForm
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function buildFormSubmit($data);
}