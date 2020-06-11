<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request;

use Aheadworks\Rma\Api\Data\RequestPrintLabelInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use \Magento\Framework\Api\AttributeValueFactory;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Class Address
 *
 * @package Aheadworks\Rma\Model\Request
 */
class PrintLabel extends AbstractExtensibleObject implements RequestPrintLabelInterface
{
    /**
     * @var AddressMetadataInterface
     */
    private $metadataService;

    /**
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param AddressMetadataInterface $metadataService
     * @param array $data
     */
    public function __construct(
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $attributeValueFactory,
        AddressMetadataInterface $metadataService,
        $data = []
    ) {
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
        $this->metadataService = $metadataService;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomAttributesCodes()
    {
        if ($this->customAttributesCodes === null) {
            $this->customAttributesCodes = $this->getEavAttributesCodes($this->metadataService);
        }
        return $this->customAttributesCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegion()
    {
        return $this->_get(self::REGION);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryId()
    {
        return $this->_get(self::COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreet()
    {
        return $this->_get(self::STREET);
    }

    /**
     * {@inheritdoc}
     */
    public function setStreet(array $street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompany()
    {
        return $this->_get(self::COMPANY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompany($company)
    {
        return $this->setData(self::COMPANY, $company);
    }

    /**
     * {@inheritdoc}
     */
    public function getTelephone()
    {
        return $this->_get(self::TELEPHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * {@inheritdoc}
     */
    public function getFax()
    {
        return $this->_get(self::FAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setFax($fax)
    {
        return $this->setData(self::FAX, $fax);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostcode()
    {
        return $this->_get(self::POSTCODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->_get(self::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstname($firstName)
    {
        return $this->setData(self::FIRSTNAME, $firstName);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($lastName)
    {
        return $this->setData(self::LASTNAME, $lastName);
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlename()
    {
        return $this->_get(self::MIDDLENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlename($middleName)
    {
        return $this->setData(self::MIDDLENAME, $middleName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuffix()
    {
        return $this->_get(self::SUFFIX);
    }

    /**
     * {@inheritdoc}
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /**
     * {@inheritdoc}
     */
    public function getVatId()
    {
        return $this->_get(self::VAT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setVatId($vatId)
    {
        return $this->setData(self::VAT_ID, $vatId);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_get(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\RequestPrintLabelExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
