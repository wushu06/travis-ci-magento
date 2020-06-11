<?php

namespace Elementary\Banner\Api\Data;

/**
 * Banner Interface
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
interface BannerInterface
{
    /**
     * Banner Table
     */
    const TABLE = 'elementary_banner';

    /**
     * Banner/Slide link table
     */
    const TABLE_BANNER_SLIDE = 'elementary_banner_slide';

    /**
     * Id
     */
    const BANNER_ID = 'banner_id';

    /**
     * Identifier
     */
    const IDENTIFIER = 'identifier';

    /**
     * Status
     */
    const STATUS = 'status';

    /**
     * Position
     */
    const POSITION = 'position';

    /**
     * Get Identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set Identifier
     *
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier($identifier);

    /**
     * Set Status
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status);
}
