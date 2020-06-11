<?php

namespace Elementary\Banner\Model;

use Elementary\Banner\Api\Data\SlideInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Slide Model
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Slide extends AbstractModel implements SlideInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'elementary_slide';

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = self::SLIDE_ID;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Slide::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlTitle()
    {
        return $this->getData(self::URL_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getShowButton()
    {
        return $this->getData(self::SHOW_BUTTON);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonTitle()
    {
        return $this->getData(self::BUTTON_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartAt()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getFinishAt()
    {
        return $this->getData(self::FINISH_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups()
    {
        return $this->getResource()->getCustomerGroups($this->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->setData(self::TITLE, $title);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->setData(self::CONTENT, $content);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrl($url)
    {
        $this->setData(self::URL, $url);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrlTitle($urlTitle)
    {
        $this->setData(self::URL_TITLE, $urlTitle);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setShowButton($showButton)
    {
        $this->setData(self::SHOW_BUTTON, $showButton);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setButtonTitle($buttonTitle)
    {
        $this->setData(self::BUTTON_TITLE, $buttonTitle);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setImage($image)
    {
        $this->setData(self::IMAGE, $image);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStartAt($startAt)
    {
        $this->setData(self::START_DATE, $startAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFinishAt($finishAt)
    {
        $this->setData(self::FINISH_DATE, $finishAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroups($customerGroups)
    {
        if (!is_array($customerGroups)) {
            $customerGroups = explode(',', $customerGroups);
        }

        $this->setData('customer_groups', $customerGroups);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [
            self::CACHE_TAG . '_' . $this->getId()
        ];
    }
}
