<?php


namespace Elementary\EmployeesManager\Model\Cache;

/**
 * Class EmployeesManager
 *
 * @package Elementary\EmployeesManager\Model\Cache
 */
class EmployeesManager extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{

    const TYPE_IDENTIFIER = 'employeesmanager_cache_tag';
    const CACHE_TAG = 'EMPLOYEESMANAGER_CACHE_TAG';

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}

