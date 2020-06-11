<?php

namespace Elementary\Banner\Block\Adminhtml\Banner\Edit\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\DataObject;
use Elementary\Banner\Api\Data\BannerInterface;
use Elementary\Banner\Api\Data\SlideInterface;
use Elementary\Banner\Model\ResourceModel\Slide\Collection;
use Elementary\Banner\Model\ResourceModel\Slide\CollectionFactory;

/**
 * Banner Slides Tab
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Slides extends Extended
{
    /**
     * Slides Collection Factory
     *
     * @var CollectionFactory
     */
    protected $_slideCollectionFactory;

    /**
     * Slides constructor
     *
     * @param Context           $context
     * @param Data              $backendHelper
     * @param CollectionFactory $slideCollectionFactory
     * @param array             $data
     */
    public function __construct(
        Context           $context,
        Data              $backendHelper,
        CollectionFactory $slideCollectionFactory,
        array             $data
    ) {
        $this->_slideCollectionFactory = $slideCollectionFactory;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('slideGrid');
        $this->setDefaultSort('slide_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Get Banner Slides
     *
     * This method is used as a callback method when the serialized grid is first instantiated,
     * and will load the currency slides that are assigned to the banner.
     *
     * @return array
     */
    public function getBannerSlides()
    {
        $slideIds = [];
        $bannerId = $this->getRequest()->getParam('banner_id', null);
        if (!$bannerId) {
            return [];
        }
        /** @var Collection $slideCollection */
        $slideCollection = $this->_slideCollectionFactory->create();
        $slideCollection->getSelect()->join(
            ['slides' => BannerInterface::TABLE_BANNER_SLIDE],
            sprintf('slides.slide_id = main_table.slide_id and slides.banner_id = %d', $bannerId),
            ['position']
        );

        foreach ($slideCollection as $slide) {
            $slideIds[$slide->getId()] = [
                'position' => $slide->getData('position')
            ];
        }

        return $slideIds;
    }

    /**
     * Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Banner Slides');
    }

    /**
     * Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Banner Slides');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return true;
    }

    /**
     * Grid Url for reload
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/slidesgrid', [
            '_current' => true
        ]);
    }

    /**
     * Grid Row Url
     *
     * @param DataObject $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Grid Collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->_slideCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['slides' => BannerInterface::TABLE_BANNER_SLIDE],
            'slides.slide_id = main_table.slide_id',
            ['position' => 'IF(slides.position IS NULL, 0, slides.position)']
        );

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * Prepare Grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_banner', [
            'type'  => 'checkbox',
            'name'  => 'in_banner',
            'align' => 'center',
            'index' => 'slide_id',
            'values' => $this->_getSelectedSlides(),
        ]);

        $this->addColumn(SlideInterface::TITLE, [
            'header' => __('Title'),
            'type'   => 'text',
            'index'  => SlideInterface::TITLE
        ]);

        $this->addColumn(SlideInterface::START_DATE, [
            'header' => __('Start Date'),
            'type'   => 'datetime',
            'index'  => SlideInterface::START_DATE
        ]);

        $this->addColumn(SlideInterface::FINISH_DATE, [
            'header' => __('Finish Date'),
            'type'   => 'datetime',
            'index'  => SlideInterface::FINISH_DATE
        ]);

        $this->addColumn(SlideInterface::STATUS, [
            'header' => __('Status'),
            'type'   => 'number',
            'index'  => SlideInterface::STATUS
        ]);

        $this->addColumn('position', [
            'header'         => __('Sort Order (ASC)'),
            'validate_class' => 'validate-number',
            'index'          => 'position',
            'type'           => 'number',
            'editable'       => true,
            'edit_only'      => $this->getRequest()->getParam('banner_id', null)
        ]);

        parent::_prepareColumns();

        return $this;
    }

    /**
     * Get Selected Slides
     *
     * @return array
     */
    protected function _getSelectedSlides()
    {
        $banners = $this->getSlides();
        if (!is_array($banners)) {
            $banners = array_keys($this->getBannerSlides());
        }

        return $banners;
    }
}
