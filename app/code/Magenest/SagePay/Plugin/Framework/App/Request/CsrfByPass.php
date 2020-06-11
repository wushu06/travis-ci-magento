<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Plugin\Framework\App\Request;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;

class CsrfByPass
{
    const BY_PASS_URI = '/sagepay/server/notify';

    public function aroundValidate(
        \Magento\Framework\App\Request\CsrfValidator $validator,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    ){
        /** @var State $appState */
        $appState = ObjectManager::getInstance()->get(State::class);
        try {
            $areaCode = $appState->getAreaCode();
        } catch (LocalizedException $exception) {
            $areaCode = null;
        }

        if ($request instanceof HttpRequest
            && in_array(
                $areaCode,
                [Area::AREA_FRONTEND, Area::AREA_ADMINHTML],
                true
            )
        ) {
            if ($request->getPathInfo() == self::BY_PASS_URI) {
                return true;
            } else
                $proceed($request, $action);
        }
    }
}