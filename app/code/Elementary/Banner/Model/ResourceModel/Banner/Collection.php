<?php

namespace Elementary\Banner\Model\ResourceModel\Banner;

use Elementary\Banner\Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Banner Collection Model
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $_idFieldName = Model\Banner::BANNER_ID;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Model\Banner::class, Model\ResourceModel\Banner::class);
        $this->_map['fields']['banner_id'] = 'main_table.banner_id';
    }

    public function addBannerIdsFilter($bannerIds, $exclude = false)
    {
        $this->addFieldToFilter('main_table.banner_id', [$exclude ? 'nin' : 'in' => $bannerIds]);
        return $this;
    }
}
