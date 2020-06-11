<?php

namespace Elementary\Banner\Setup;

use Elementary\Banner\Api\Data\SlideInterface;
use Elementary\Banner\Model\ResourceModel\Slide\Collection;
use Elementary\Banner\Model\ResourceModel\Slide\CollectionFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Group Collection Factory
     *
     * @var CollectionFactory
     */
    protected $_slideCollectionFactory;

    /**
     * UpgradeData constructor
     *
     * @param CollectionFactory $slideCollectionFactory
     */
    public function __construct(
        CollectionFactory $slideCollectionFactory
    ) {
        $this->_slideCollectionFactory = $slideCollectionFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        switch (true) {
            case !$context->getVersion():
            case version_compare($context->getVersion(), '1.0.2', '<'):
                $this->_addSlideCustomerGroupData($setup);
                break;
        }
    }

    /**
     * Add Customer groups for slides
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    protected function _addSlideCustomerGroupData(ModuleDataSetupInterface $setup)
    {
        /** @var Collection $groups */
        $inserts = [];
        $groups = $this->_slideCollectionFactory->create();
        foreach ($groups->getAllIds() as $slideId) {
            $inserts[] = [
                'slide_id'       => $slideId,
                'customer_group' => 0
            ];
        }

        $setup->getConnection()->insertMultiple(SlideInterface::TABLE_CUSTOMER_GROUP, $inserts);
    }
}
