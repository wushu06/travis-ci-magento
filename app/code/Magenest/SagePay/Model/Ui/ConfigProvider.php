<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    protected $_helper;

    protected $_cardFactory;

    protected $_customerSession;

    protected $_checkoutSession;

    protected $sageHelper;

    protected $_urlBuilder;

    protected $sageHelperMoto;

    protected $scopeConfig;

    protected $ccTypeSource;

    const CODE = 'magenest_sagepay';

    const DIRECT_CODE = 'magenest_sagepay_direct';

    const PAYPAL_CODE = 'magenest_sagepay_paypal';

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magenest\SagePay\Helper\SageHelper $sageHelper,
        \Magenest\SagePay\Model\CardFactory $cardFactory,
        \Magenest\SagePay\Helper\SageHelperMoto $sageHelperMoto,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magenest\SagePay\Model\Source\SageCctype $cctype
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_urlBuilder = $urlBuilder;
        $this->sageHelper = $sageHelper;
        $this->_cardFactory = $cardFactory;
        $this->sageHelperMoto = $sageHelperMoto;
        $this->ccTypeSource = $cctype;
    }

    public function getConfig()
    {
        $cardData = $this->getDataCard();
        return [
            'payment' => [
                "magenest_sagepay"=>[
                    'isSave' => (boolean)$this->sageHelper->getCanSave(),
                    'isGiftAid' => (boolean)$this->sageHelper->isGiftAid(),
                    'isSandbox' => (boolean)$this->sageHelper->getIsSandbox(),
                    'instruction' => $this->sageHelper->getInstructions(),
                    'saveCards' => json_encode($cardData),
                    'hasCard' => count($cardData)>0 ? true:false,
                    'useDropin' => (boolean)$this->sageHelper->useDropIn(),
                    'dropinMode' => $this->sageHelper->getDropInMode()
                ],
                "magenest_sagepay_direct" => [
                    'isSave' => (boolean)$this->sageHelper->getCanSave(),
                    'isGiftAid' => (boolean)$this->sageHelper->isGiftAid(),
                    'isSandbox' => (boolean)$this->sageHelper->getIsSandbox(),
                    'instruction' => $this->sageHelper->getInstructions(),
                    'saveCards' => json_encode($cardData),
                    'cardType' => json_encode($this->getAllowCardTypesConfig()),
                    'hasCard' => count($cardData) > 0 ? true : false,
                    'useDropin' => (boolean)$this->sageHelper->useDropIn(),
                ],
                "magenest_sagepay_paypal" => [
                    'redirect_url' => $this->getUrl('sagepay/paypal/redirect')
                ]
            ]
        ];
    }

    public function getDataCard()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getCustomerId();
            return $this->_cardFactory->create()->loadCards($customerId);
        } else {
            return [];
        }
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    public function getAllowCardTypesConfig()
    {
        $returnOptions = [];
        $config = $this->scopeConfig->getValue('payment/magenest_sagepay_direct/cctypes', ScopeInterface::SCOPE_STORE);
        $allowType = explode(',',$config);
        $allType = $this->ccTypeSource->getAllSageCardType();
        if(is_array($allowType)){
            foreach ($allowType as $value){
                if(isset($allType[$value])){
                    $returnOptions[] =
                        [
                            'value' => $value,
                            'label' => $allType[$value]
                        ]
                    ;
                }
            }
        }
        return $returnOptions;
    }
}
