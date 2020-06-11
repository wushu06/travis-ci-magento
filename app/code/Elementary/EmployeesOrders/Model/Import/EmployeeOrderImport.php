<?php
namespace Elementary\EmployeesOrders\Model\Import;

use Elementary\EmployeesOrders\Api\Data\EmployeeOrderInterfaceFactory;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Psr\Log\LoggerInterface;
use Elementary\EmployeesOrders\Api\EmployeeOrderRepositoryInterface;
use Elementary\EmployeesOrders\Model\EmployeeOrder;
use Elementary\EmployeesOrders\Model\Import\RowValidatorInterface as ValidatorInterface;

class EmployeeOrderImport extends AbstractEntity
{
    const TABLE_ENTITY = 'elementary_employees_manager_employee_order';
    const ID = 'employee_order_id';
    const ORDER_ID = 'order_id';
    const EMPLOYEE_ID = 'employee_id';
    const ITEM_ID = 'item_id';


    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_ID_IS_EMPTY => 'Empty',
    ];

    protected $_permanentAttributes = [self::ID];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = false;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::ID,
        self::ORDER_ID,
        self::EMPLOYEE_ID,
        self::ITEM_ID,
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    protected $_validators = [];

    /**
     * @var DateTime
     */
    protected $_connection;

    protected $_resource;
    /**
     * @var EmployeeOrderRepositoryInterface
     */
    private $employeeOrderRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EmployeeOrderInterfaceFactory
     */
    private $employeeOrderFactory;

    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        EmployeeOrderRepositoryInterface $employeeOrderRepository,
        EmployeeOrderInterfaceFactory $employeeOrderFactory,
        LoggerInterface $logger
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->employeeOrderRepository = $employeeOrderRepository;
        $this->logger = $logger;
        $this->employeeOrderFactory = $employeeOrderFactory;
    }

    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'selection_employees_orders';
    }

    /**
     * Create advanced question data from raw data.
     *
     * @return bool Result of operation.
     * @throws Exception
     */
    protected function _importData()
    {
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteEntity();
        } elseif (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceEntity();
        } elseif (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveEntity();
        }
        return true;
    }

    /**
     * Deletes question data from raw data.
     *
     * @return $this
     */
    public function deleteEntity()
    {
        $ids = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowId = $rowData[self::ID];
                    $ids[] = $rowId;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($ids) {
            $this->deleteEntityFinish(array_unique($ids), self::TABLE_ENTITY);
        }
        return $this;
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        return true;
        $title = false;
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        if (!isset($rowData[self::ID]) || empty($rowData[self::ID])) {
            $this->addRowError(ValidatorInterface::ERROR_MESSAGE_IS_EMPTY, $rowNum);
            return false;
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    protected function deleteEntityFinish(array $ids, $table)
    {
        if ($table && $ids) {
            try {
                $this->countItemsDeleted += $this->_connection->delete(
                    $this->_connection->getTableName($table),
                    $this->_connection->quoteInto('entity_id IN (?)', $ids)
                );
                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Replace question
     *
     * @return $this
     */
    public function replaceEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Save and replace question
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $ids = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_MESSAGE_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $rowId = $rowData[self::ID];
                $ids[] = $rowId;
                $entityList[$rowId][] = [
                    self::ID => $rowData[self::ID],
                    self::EMPLOYEE_ID => $rowData[self::EMPLOYEE_ID],
                    self::ORDER_ID => $rowData[self::ORDER_ID],
                    self::ITEM_ID => $rowData[self::ITEM_ID],
                ];
            }
            if (Import::BEHAVIOR_REPLACE == $behavior) {
                if ($ids) {
                    if ($this->deleteEntityFinish(array_unique($ids), self::TABLE_ENTITY)) {
                        $this->saveEntityFinish($entityList, self::TABLE_ENTITY);
                    }
                }
            } elseif (Import::BEHAVIOR_APPEND == $behavior) {
                $this->saveEntityFinish($entityList, self::TABLE_ENTITY);
            }
        }
        return $this;
    }

    /**
     * Save question
     *
     * @param array $priceData
     * @param string $table
     * @return $this
     */
    protected function saveEntityFinish(array $entityData, $table)
    {

        if ($entityData) {
            $tableName = $this->_connection->getTableName($table);
            $entityIn = [];


            foreach ($entityData as $id => $entityRows) {
                foreach ($entityRows as $row) {
                    unset($row[self::ID]);
                    $model = $this->employeeOrderFactory->create();
                    $model->setData($row)
                        ->save();
                    $entityIn[] = $row;
                }
            }
            if ($entityIn) {

                /*$this->_connection->insertOnDuplicate($tableName, $entityIn,[
                    self::ID,
                    self::NAME,
                    self::COMMENT
                ]);*/
            }
        }
        return $this;
    }

    /**
     * Save question
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
}
