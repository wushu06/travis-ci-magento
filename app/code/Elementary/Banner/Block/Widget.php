<?php

namespace Elementary\Banner\Block;

use Elementary\Banner\Helper\Data;
use Elementary\Banner\Model\Banner;
use Elementary\Banner\Model\BannerFactory;
use Elementary\Banner\Model\ResourceModel\Slide\Collection;
use Elementary\Banner\Model\ResourceModel\Slide\CollectionFactory;
use Elementary\Banner\Model\Slide;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Banner Widget
 *
 * @method int getBannerId()
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Widget extends Template implements BlockInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Elementary_Banner::widget.phtml';

    /**
     * Banner Factory
     *
     * @var BannerFactory
     */
    protected $_bannerFactory;

    /**
     * Slide Collection Factory
     *
     * @var CollectionFactory
     */
    protected $_slideCollectionFactory;

    /**
     * Banner Helper
     *
     * @var Data
     */
    protected $_helper;

    /**
     * FilterProvider
     *
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * Customer Session Factory
     *
     * @var SessionFactory
     */
    protected $_customerSessionFactory;

    /**
     * Banner Entity
     *
     * @var Banner|null
     */
    protected $_banner;

    /**
     * Slide Collection
     *
     * @var Slide[]|Collection|null
     */
    protected $_slides;

    /**
     * Widget constructor
     *
     * @param Template\Context  $context
     * @param BannerFactory     $bannerFactory
     * @param CollectionFactory $slideCollectionFactory
     * @param Data              $helper
     * @param FilterProvider    $filterProvider
     * @param SessionFactory    $customerSessionFactory
     * @param array             $data
     */
    public function __construct(
        Template\Context  $context,
        BannerFactory     $bannerFactory,
        CollectionFactory $slideCollectionFactory,
        Data              $helper,
        FilterProvider    $filterProvider,
        SessionFactory    $customerSessionFactory,
        array             $data = []
    ) {
        parent::__construct($context, $data);
        $this->_bannerFactory = $bannerFactory;
        $this->_slideCollectionFactory = $slideCollectionFactory;
        $this->_helper = $helper;
        $this->_filterProvider = $filterProvider;
        $this->_customerSessionFactory = $customerSessionFactory;
    }

    /**
     * Get Banner Entity
     *
     * @return Banner|null
     */
    public function getBanner()
    {
        if (!$this->_banner) {
            /** @var Banner $bannerModel */
            $bannerModel = $this->_bannerFactory->create();
            $banner = $bannerModel->load($this->getBannerId());
            $this->_banner = $banner;
        }

        return $this->_banner;
    }

    /**
     * Get Banner Slide Collection
     *
     * @return Collection|Slide[]|null
     */
    public function getSlides()
    {
        if (!$this->_slides) {
            /** @var Collection $slides */
            $slides = $this->_slideCollectionFactory->create();
            $slides->addIsVisibleFilter();
            $slides->addBannerIdFilter((int) $this->getBannerId());
            $slides->addCustomerGroupFilter($this->_getCustomerGroupId());
            $this->_slides = $slides;
        }

        return $this->_slides;
    }

    /**
     * Get The Slide Image
     *
     * @param Slide $slide
     *
     * @return string
     */
    public function getSlideImage(Slide $slide)
    {
        return $this->_helper->getSlideImageUrl($slide->getImage());
    }

    /**
     * Get content of slide
     *
     * @param Slide $slide
     *
     * @return string
     */
    public function getSlideContent(Slide $slide)
    {
        return $this->_filterProvider->getBlockFilter()->filter($slide->getContent());
    }

    /**
     * Check that the banner is enabled and slides are available before displaying widget
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if (!$this->getBanner()->getId() || !$this->getBanner()->getStatus()) {
            $this->setTemplate(null);

            return $this;
        }

        if (!$this->getSlides()->count()) {
            $this->setTemplate(null);

            return $this;
        }

        parent::_beforeToHtml();

        return $this;
    }

    /**
     * Get Customer Group Id
     *
     * @return int
     */
    protected function _getCustomerGroupId()
    {
        /** @var Session $session */
        $session = $this->_customerSessionFactory->create();

        return (int) $session->getCustomerGroupId();
    }
}
