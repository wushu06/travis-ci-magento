<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Plugin\Framework\Data\Form\FormKey;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;

class Validator{

    protected $request;

    protected $handlePath;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        $handlePath = []
    )
    {
        $this->handlePath = $handlePath;
        $this->request = $request;
    }

    public function afterValidate(\Magento\Framework\Data\Form\FormKey\Validator $object, $result){
        try {
            /** @var State $appState */
            $appState = ObjectManager::getInstance()->get(State::class);
            $areaCode = $appState->getAreaCode();
            if($areaCode == Area::AREA_FRONTEND){
                if(in_array(trim($this->request->getPathInfo(),'/'),$this->handlePath)){
                    return true;
                }
            }
        }catch (\Exception $exception){
        }
        return $result;
    }
}