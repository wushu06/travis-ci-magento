<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Request print label interface
 * @api
 */
interface RequestPrintLabelInterface extends CustomAttributesDataInterface
{
    /**#@+
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    const REGION = 'region';
    const REGION_ID = 'region_id';
    const COUNTRY_ID = 'country_id';
    const STREET = 'street';
    const COMPANY = 'company';
    const TELEPHONE = 'telephone';
    const FAX = 'fax';
    const POSTCODE = 'postcode';
    const CITY = 'city';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const MIDDLENAME = 'middlename';
    const PREFIX = 'prefix';
    const SUFFIX = 'suffix';
    const VAT_ID = 'vat_id';
    /**#@-*/

    /**
     * Get region
     *
     * @return string|null
     */
    public function getRegion();

    /**
     * Set region
     *
     * @param string|null $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Get region ID
     *
     * @return int|null
     */
    public function getRegionId();

    /**
     * Set region ID
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Two-letter country code in ISO_3166-2 format
     *
     * @return string|null
     */
    public function getCountryId();

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * Get street
     *
     * @return string[]|null
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param string[] $street
     * @return $this
     */
    public function setStreet(array $street);

    /**
     * Get company
     *
     * @return string|null
     */
    public function getCompany();

    /**
     * Set company
     *
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * Get telephone number
     *
     * @return string|null
     */
    public function getTelephone();

    /**
     * Set telephone number
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax number
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get postcode
     *
     * @return string|null
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Get city name
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Set city name
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstname($firstName);

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastname($lastName);

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename();

    /**
     * Set middle name
     *
     * @param string $middleName
     * @return $this
     */
    public function setMiddlename($middleName);

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this|null
     */
    public function setSuffix($suffix);

    /**
     * Get Vat id
     *
     * @return string|null
     */
    public function getVatId();

    /**
     * Set Vat id
     *
     * @param string $vatId
     * @return $this
     */
    public function setVatId($vatId);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Rma\Api\Data\RequestPrintLabelExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Rma\Api\Data\RequestPrintLabelExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Rma\Api\Data\RequestPrintLabelExtensionInterface $extensionAttributes
    );
}
