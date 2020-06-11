<?php

namespace Elementary\Banner\Model;

use Elementary\Banner\Api\Data\BannerInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Banner Model
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Banner extends AbstractModel implements BannerInterface, IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'elementary_banner';

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
    protected $_idFieldName = self::BANNER_ID;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Banner::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
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
    public function setIdentifier($identifier)
    {
        $this->setData(self::IDENTIFIER, $identifier);

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

    /**
     * Remove slides from banner
     *
     * @param $bannerId
     *
     * @return void
     */
    public function unlinkSlidesFromBanner($bannerId)
    {
        $resource = $this->getResource();

        $resource->getConnection()->delete(self::TABLE_BANNER_SLIDE, [
            'banner_id = ?' => $bannerId
        ]);
    }

    /**
     * Link a slide to a banner
     *
     * @param int $bannerId
     * @param int $slideId
     * @param int $position
     *
     * @return void
     */
    public function linkSlideToBanner($bannerId, $slideId, $position = 0)
    {
        $resource = $this->getResource();

        $resource->getConnection()->insert(self::TABLE_BANNER_SLIDE, [
            'banner_id' => $bannerId,
            'slide_id'  => $slideId,
            'position'  => $position
        ]);
    }
}
