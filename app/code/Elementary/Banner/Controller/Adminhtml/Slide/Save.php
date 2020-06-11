<?php

namespace Elementary\Banner\Controller\Adminhtml\Slide;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\MediaStorage\Model\File\Uploader;
use Elementary\Banner\Controller\Adminhtml\AbstractAction;
use Elementary\Banner\Model\Slide;

/**
 * Save Action Controller
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Save extends AbstractAction
{
    /**
     * Slide Save Action
     *
     * @return Redirect
     */
    public function execute()
    {
        $slideId = $this->getRequest()->getParam('slide_id', null);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            /** @var Slide $slideModel */
            $slideModel = $this->_slideFactory->create();

            if ($slideId) {
                $slideModel->load($slideId);
            }

            $data = $this->getRequest()->getParams();
            if ($this->isSlideImageAttached('image')) {
                $data['image'] = $this->uploadSlideImage('image');
            } else {
                unset($data['image']);
            }

            $data['start_date'] = $this->formatDate($data['start_date']);
            $data['finish_date'] = $this->formatDate($data['finish_date']);

            $slideModel->setData($data)->save();
            $this->messageManager->addSuccess(__('The Slide has been saved'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', [
                    'slide_id' => $slideId
                ]);
            }

            return $resultRedirect->setPath('*/*/');

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * Check Slider Image
     *
     * Check if a slider image has been submitted in the form
     *
     * @param string $inputName File Input Name
     *
     * @return bool
     */
    private function isSlideImageAttached($inputName)
    {
        return $_FILES[$inputName]['name'] !== '' && $_FILES[$inputName]['size'] > 0;
    }

    /**
     * Upload Slide Image
     *
     * Upload a Slide image to the media directory
     *
     * @param string $inputName File Input name
     *
     * @return string
     *
     * @throws \Exception
     */
    private function uploadSlideImage($inputName)
    {
        /** @var Read $mediaDirectory */
        $mediaDirectory = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        /** @var Uploader $uploader */
        $uploader = $this->_uploaderFactory->create([
            'fileId' => $inputName
        ]);
        $uploader->setAllowedExtensions([
            'jpg', 'jpeg', 'gif', 'png'
        ]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);

        $result = $uploader->save($mediaDirectory->getAbsolutePath(Slide::SLIDE_PATH));

        return Slide::SLIDE_PATH . $result['file'];
    }

    /**
     * Format Date
     *
     * @param string $dateInput Date Input
     *
     * @return string
     */
    private function formatDate($dateInput)
    {
        /** @var \DateTime $dateTime */
        $dateTime = $this->_timeZone->date($dateInput);

        return $dateTime->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    /**
     * Check permission for slide save
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ACL_RESOURCE);
    }
}
