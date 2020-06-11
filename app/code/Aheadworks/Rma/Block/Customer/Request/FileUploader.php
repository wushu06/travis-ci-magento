<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request;

use Aheadworks\Rma\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class FileUploader
 *
 * @package Aheadworks\Rma\Block
 */
class FileUploader extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/file_uploader.phtml';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        if (isset($this->jsLayout['components']['awRmaFileUploader'])) {
            $this->jsLayout['components']['awRmaFileUploader']['uploaderConfig'] = [
                'url' => $this->getFileUploadUrl()
            ];
            $this->jsLayout['components']['awRmaFileUploader'] = array_merge(
                $this->jsLayout['components']['awRmaFileUploader'],
                [
                    'maxFileSize' => $this->config->getMaxUploadFileSize(),
                    'allowedExtensions' => $this->config->getAllowFileExtensions(),
                    'notice' => $this->getNotice()
                ]
            );
        }

        return parent::getJsLayout();
    }

    /**
     * Check if can show block
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->config->isAllowCustomerAttachFiles();
    }

    /**
     * Retrieve file upload url
     *
     * @return string
     */
    private function getFileUploadUrl()
    {
        return $this->getUrl('*/*/upload', ['_secure' => true]);
    }

    /**
     * Retrieve notice
     *
     * @return \Magento\Framework\Phrase|string
     */
    private function getNotice()
    {
        if (!empty($this->config->getAllowFileExtensions())) {
            $fileTypes = implode(', ', $this->config->getAllowFileExtensions());
            return __('The following file types are allowed: %1', $fileTypes);
        }

        return '';
    }
}
