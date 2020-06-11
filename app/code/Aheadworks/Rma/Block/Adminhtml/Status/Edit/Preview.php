<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Adminhtml\Status\Edit;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Model\Email\EmailMetadataInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Aheadworks\Rma\Model\Request\Email\Previewer;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\TemplateInterface;

/**
 * Class Preview
 *
 * @method int getStoreId()
 * @method Preview setStoreId(int $storeId)
 * @method StatusInterface getStatus()
 * @method Preview setStatus(StatusInterface $status)
 * @method bool getToAdmin()
 * @method Preview setToAdmin(bool $toAdmin)
 * @package Aheadworks\Rma\Block\Adminhtml\Status\Edit
 */
class Preview extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::status/preview.phtml';

    /**
     * @var Previewer
     */
    private $previewer;

    /**
     * @var FactoryInterface
     */
    private $templateFactory;

    /**
     * @var EmailMetadataInterface
     */
    private $preview;

    /**
     * @var TemplateInterface
     */
    private $emailTemplate;

    /**
     * @param Context $context
     * @param Previewer $previewer
     * @param FactoryInterface $templateFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Previewer $previewer,
        FactoryInterface $templateFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->previewer = $previewer;
        $this->templateFactory = $templateFactory;
    }

    /**
     * Retrieve preview data
     *
     * @return EmailMetadataInterface
     */
    public function getPreview()
    {
        if (null == $this->preview) {
            $this->preview = $this->previewer->preview($this->getStoreId(), $this->getStatus(), $this->getToAdmin());
        }

        return $this->preview;
    }

    /**
     * Retrieve subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->renderTemplate()->getSubject();
    }

    /**
     * Retrieve content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->renderTemplate()->processTemplate();
    }

    /**
     * Render template
     *
     * @return TemplateInterface
     */
    private function renderTemplate()
    {
        if (null == $this->emailTemplate) {
            $this->emailTemplate = $this->templateFactory->get($this->getPreview()->getTemplateId())
                ->setVars($this->getPreview()->getTemplateVariables())
                ->setOptions($this->getPreview()->getTemplateOptions());
            $this->emailTemplate->processTemplate();
        }

        return $this->emailTemplate;
    }
}
