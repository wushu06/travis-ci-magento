<?php

namespace Elementary\Banner\Api\Data;

/**
 * Slide Interface
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
interface SlideInterface
{
    /**
     * Slide Table
     */
    const TABLE = 'elementary_slide';

    /**
     * Slide Customer Group Table
     */
    const TABLE_CUSTOMER_GROUP = 'elementary_slide_customer_group';

    /**
     * Id
     */
    const SLIDE_ID = 'slide_id';

    /**
     * Title
     */
    const TITLE = 'title';

    /**
     * Content
     */
    const CONTENT = 'content';

    /**
     * Url
     */
    const URL = 'url';

    /**
     * Url Title
     */
    const URL_TITLE = 'url_title';

    /**
     * Show Button
     */
    const SHOW_BUTTON = 'show_button';

    /**
     * Button Title
     */
    const BUTTON_TITLE = 'button_title';

    /**
     * Image
     */
    const IMAGE = 'image';

    /**
     * Created At
     */
    const CREATED_AT = 'created_at';

    /**
     * Updated At
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Start Date
     */
    const START_DATE = 'start_date';

    /**
     * Finish Date
     */
    const FINISH_DATE = 'finish_date';

    /**
     * Status
     */
    const STATUS = 'status';

    /**
     * Slide Path
     */
    CONST SLIDE_PATH = 'elementary/banner/slides';

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get Content
     *
     * @return string
     */
    public function getContent();

    /**
     * Get Url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get Url Title
     *
     * @return string
     */
    public function getUrlTitle();

    /**
     * Get Show Button
     *
     * @return int
     */
    public function getShowButton();

    /**
     * Get Button Title
     *
     * @return string
     */
    public function getButtonTitle();

    /**
     * Get Image
     *
     * @return string
     */
    public function getImage();

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Get Updated At
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Get Start At
     *
     * @return string
     */
    public function getStartAt();

    /**
     * Get Finish At
     *
     * @return string
     */
    public function getFinishAt();

    /**
     * Get Customer Groups
     *
     * @return int[]
     */
    public function getCustomerGroups();

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set Title
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title);

    /**
     * Set Content
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content);

    /**
     * Set Url
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url);

    /**
     * Set Url Title
     *
     * @param string $urlTitle
     *
     * @return $this
     */
    public function setUrlTitle($urlTitle);

    /**
     * Set Show Button
     *
     * @param string $showButton
     *
     * @return $this
     */
    public function setShowButton($showButton);

    /**
     * Set Button Title
     *
     * @param string $buttonTitle
     *
     * @return $this
     */
    public function setButtonTitle($buttonTitle);

    /**
     * Set Image
     *
     * @param string $image
     *
     * @return $this
     */
    public function setImage($image);

    /**
     * Set Created At
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set Start At Date
     *
     * @param string $startAt
     *
     * @return $this
     */
    public function setStartAt($startAt);

    /**
     * Set Finish At Date
     *
     * @param string $finishAt
     *
     * @return $this
     */
    public function setFinishAt($finishAt);

    /**
     * Get Customer Groups
     *
     * @param int[] $customerGroups
     *
     * @return $this;
     */
    public function setCustomerGroups($customerGroups);

    /**
     * Set Status
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status);
}
