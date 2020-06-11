<?php

namespace Elementary\Banner\Controller\Adminhtml\Banner;

use Elementary\Banner\Controller\Adminhtml\AbstractAction;
use Elementary\Banner\Model\Banner;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Save Controller
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Save extends AbstractAction
{
    /**
     * Save Action
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $redirect = $this->resultRedirectFactory->create();

        try {
            /** @var Banner $banner */
            $banner = $this->_bannerFactory->create();
            $banner->setData($data);
            $banner->save();

            $this->messageManager->addSuccess(__('%1 has been successfully %2.',
                $banner->getIdentifier(),
                $banner->isObjectNew() ? __('added') : __('edited')
            ));

            $bannerId = $banner->getId();
            /** @var Banner $bannerModel */
            $bannerModel = $this->_bannerFactory->create();
            $banner->unlinkSlidesFromBanner($bannerId);
            if (isset($data['links'])) {
                $slideIds = $this->_jsHelper->decodeGridSerializedInput($data['links']['slides']);
                foreach ($slideIds as $slideId => $slide) {
                    $bannerModel->linkSlideToBanner($bannerId, $slideId, $slide['position']);
                }
            }

            if ($this->getRequest()->getParam('back')) {
                return $redirect->setPath('*/*/edit', [
                    'banner_id' => (int) $banner->getId()
                ]);
            }

        } catch (\Exception $e) {
            $this->messageManager->addError(__('This banner could not be saved (%1).',
                $e->getMessage())
            );
        }

        return $redirect->setPath('*/*/index');
    }
}
