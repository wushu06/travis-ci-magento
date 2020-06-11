<?php

namespace Elementary\Banner\Controller\Adminhtml;

use Elementary\Banner\Model;
use Magento\Backend\App\Action;
use Magento\Backend\Helper\Js;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Abstract Controller
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
abstract class AbstractAction extends Action
{
    /**
     * Acl Resource
     */
    const ACL_RESOURCE = 'Elementary_Banner::banner';

    /**
     * Result Forward Factory
     *
     * @var ForwardFactory
     */
    protected $_forwardFactory;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $_registry;

    /**
     * Layout Factory
     *
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * Page Factory
     *
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * Banner Factory
     *
     * @var Model\BannerFactory
     */
    protected $_bannerFactory;

    /**
     * Slide Factory
     *
     * @var Model\SlideFactory
     */
    protected $_slideFactory;

    /**
     * File System
     *
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * Uploader Factory
     *
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * Timezone
     *
     * @var Timezone
     */
    protected $_timeZone;

    /**
     * Js Helper
     *
     * @var Js
     */
    protected $_jsHelper;

    /**
     * AbstractAction constructor
     *
     * @param Action\Context      $context
     * @param ForwardFactory      $forwardFactory
     * @param Registry            $registry
     * @param LayoutFactory       $layoutFactory
     * @param PageFactory         $pageFactory
     * @param Filesystem          $filesystem
     * @param UploaderFactory     $uploaderFactory
     * @param Js                  $jsHelper
     * @param Timezone            $timeZone
     * @param Model\BannerFactory $bannerFactory
     * @param Model\SlideFactory  $slideFactory
     */
    public function __construct(
        Action\Context      $context,
        ForwardFactory      $forwardFactory,
        Registry            $registry,
        LayoutFactory       $layoutFactory,
        PageFactory         $pageFactory,
        Filesystem          $filesystem,
        UploaderFactory     $uploaderFactory,
        Js                  $jsHelper,
        Timezone            $timeZone,
        Model\BannerFactory $bannerFactory,
        Model\SlideFactory  $slideFactory
    ) {
        parent::__construct($context);
        $this->_forwardFactory = $forwardFactory;
        $this->_registry = $registry;
        $this->_layoutFactory = $layoutFactory;
        $this->_pageFactory = $pageFactory;
        $this->_fileSystem = $filesystem;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_jsHelper = $jsHelper;
        $this->_timeZone = $timeZone;
        $this->_bannerFactory = $bannerFactory;
        $this->_slideFactory = $slideFactory;
    }
}
