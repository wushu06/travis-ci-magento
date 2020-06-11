<?php
namespace Elementary\EmployeesOrders\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\AbstractModel as FrameworkAbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * @api
 */
abstract class AbstractModel extends AbstractDb
{
    /**
     * Event Manager
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * constructor
     * @param Context $context
     * @param EventManagerInterface $eventManager
     * @param mixed $connectionName
     */
    public function __construct(
        Context $context,
        EventManagerInterface $eventManager,
        $connectionName = null
    ) {
        $this->eventManager = $eventManager;
        parent::__construct($context, $connectionName);
    }


}
