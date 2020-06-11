<?php

namespace Elementary\Banner\Model\ResourceModel\Slide;

use Elementary\Banner\Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Slide Collection Model
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
    protected $_idFieldName = Model\Slide::SLIDE_ID;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Model\Slide::class, Model\ResourceModel\Slide::class);
    }

    /**
     * Add Visibility filter to collection
     *
     * @return $this
     */
    public function addIsVisibleFilter()
    {
        $dateTime = new \DateTime();
        $currentDate = $dateTime->format('Y-m-d H:i:s');

        $this->addFieldToFilter(Model\Slide::STATUS, [
            'eq' => 1
        ]);

        $this->addFieldToFilter(Model\Slide::START_DATE, [
            'lteq' => $currentDate
        ]);

        $this->addFieldToFilter(Model\Slide::FINISH_DATE, [
            'gteq' => $currentDate
        ]);

        return $this;
    }

    /**
     * Add Banner Id filter
     *
     * @param int $bannerId
     *
     * @return $this
     */
    public function addBannerIdFilter($bannerId)
    {
        $this->getSelect()->join(
            ['slides' => Model\Banner::TABLE_BANNER_SLIDE],
            sprintf('slides.slide_id = main_table.slide_id and slides.banner_id = %d', $bannerId),
            ['position']
        );

        $this->getSelect()->order('position desc');

        return $this;
    }

    /**
     * Add Customer Group filter
     *
     * @param int $customerGroupId
     *
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        $customerGroups = [32000];
        $customerGroups[] = $customerGroupId;
        
        $this->getSelect()->joinLeft(
            ['customer_group' => Model\Slide::TABLE_CUSTOMER_GROUP],
            'customer_group.slide_id = main_table.slide_id',
            ['']
        );

        $this->getSelect()->where('customer_group.customer_group in (?)', $customerGroups);

        $this->getSelect()->group('main_table.slide_id');

        return $this;
    }
}
