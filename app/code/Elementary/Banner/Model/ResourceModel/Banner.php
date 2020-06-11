<?php

namespace Elementary\Banner\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Elementary\Banner\Model;

/**
 * Banner Resource Model
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Banner extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Model\Banner::TABLE, Model\Banner::BANNER_ID);
    }
}
