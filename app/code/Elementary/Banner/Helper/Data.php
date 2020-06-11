<?php

namespace Elementary\Banner\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;

/**
 * Banner Helper
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Data extends AbstractHelper
{
    /**
     * Get Slide Image Url
     *
     * @param string $slideImage Slide Image
     *
     * @return string
     */
    public function getSlideImageUrl($slideImage)
    {
        $mediaUrl = $this->_urlBuilder->getBaseUrl([
            '_type' => UrlInterface::URL_TYPE_MEDIA
        ]);

        return $mediaUrl . $slideImage;
    }
}
