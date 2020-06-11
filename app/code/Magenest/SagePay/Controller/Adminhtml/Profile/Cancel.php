<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magenest\SagePay\Controller\Adminhtml\Profile;
use Magento\Framework\App\Action\Context;
use Magenest\SagePay\Model\ProfileFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class Cancel extends Profile
{
    protected $_profileFactory;
    protected $session;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        ProfileFactory $profileFactory,
        LoggerInterface $loggerInterface,
        Registry $registry
    ) {
        parent::__construct($context, $pageFactory, $profileFactory, $loggerInterface, $registry);
    }

    public function execute()
    {
        $profile_id = $this->getRequest()->getParam('profile_id');
        try {
            /** @var \Magenest\SagePay\Model\Profile $profile */
            $profile = $this->_profileFactory->create()->load($profile_id);
            $profile->cancelSubscription();
            $this->messageManager->addSuccessMessage(__('You cancelled the profile.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __("Error"));
        }


        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('sagepay/profile/index');
    }
}
