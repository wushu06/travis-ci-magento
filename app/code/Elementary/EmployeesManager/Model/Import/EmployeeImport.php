<?php
/**
 * Elementary Digital.
 *
 *
 * @package    Elementary
 * @author     nour@elementarydigital.co.uk
 * @copyright  Copyright (c) 2020 WeAreMagneto
 */
namespace Elementary\EmployeesManager\Model\Import;


use Elementary\EmployeesManager\Model\Import\EmployeeImport\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class EmployeeImport extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const TABLE_ENTITY = 'customer_employees';
    const ID = 'entity_id';
    const NAME = 'name';
    const STATUS = 'status';
    const PRINTED_NAME = 'printed_name';
    const DISPLAY_AREA = 'display_area';
    const COMMENT = 'comment';
    const CUSTOMER_ID = 'customer_id';
    const GROUP_ID = 'group_id';
    const CREATED_AT = 'created_at';
    const STORE_IDS = 'store_ids';
    const ORDER_ID = 'order_id';
    const ITEM_ID = 'item_id';
    const STATUS_DISABLED = 2;
    const STATUS_ENABLED = 1;

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
    protected $needColumnCheck = true;

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames = [
        self::ID,
        self::NAME,
        self::COMMENT,
        self::STATUS,
        self::CUSTOMER_ID,
        self::GROUP_ID,
        self::DISPLAY_AREA,
       // self::PRINTED_NAME
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

    protected $_validators = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_connection;

    protected $_resource;
    /**
     * @var \Selection\Employee\Api\Repository\EmployeeRepositoryInterface
     */
    private $employeeRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Elementary\EmployeesManager\Api\CustomerEmployeeRepositoryInterface $employeeRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->employeeRepository = $employeeRepository;
        $this->logger = $logger;
    }

    public function getValidColumnNames() {
        return $this->validColumnNames;
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode() {
        return 'selection_employees';
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum) {
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

    /**
     * Create advanced question data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData() {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteEntity();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->replaceEntity();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveEntity();
        }
        return true;
    }

    /**
     * Save question
     *
     * @return $this
     */
    public function saveEntity() {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Replace question
     *
     * @return $this
     */
    public function replaceEntity() {
        $this->saveAndReplaceEntity();
        return $this;
    }

    /**
     * Deletes question data from raw data.
     *
     * @return $this
     */
    public function deleteEntity() {
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
            $this->deleteEntityFinish(array_unique($ids),self::TABLE_ENTITY);
        }
        return $this;
    }

    /**
     * Save and replace question
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceEntity() {
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
                $rowId= $rowData[self::ID];
                $ids[] = $rowId;
                $entityList[$rowId][] = [
                    self::ID => $rowData[self::ID],
                    self::NAME => $rowData[self::NAME],
                    self::COMMENT => $rowData[self::COMMENT],
                    self::STATUS => $rowData[self::STATUS],
                    self::DISPLAY_AREA => $rowData[self::DISPLAY_AREA],
                    self::CUSTOMER_ID => $rowData[self::CUSTOMER_ID],
                    self::GROUP_ID => $rowData[self::GROUP_ID],
                   // self::PRINTED_NAME => $rowData[self::PRINTED_NAME]
                ];
            }
            if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
                if ($ids) {
                    if ($this->deleteEntityFinish(array_unique(  $ids), self::TABLE_ENTITY)) {
                        $this->saveEntityFinish($entityList, self::TABLE_ENTITY);
                    }
                }
            } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
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
    protected function saveEntityFinish(array $entityData, $table) {
        if ($entityData) {


            $tableName = $this->_connection->getTableName($table);
            $entityIn = [];
            foreach ($entityData as $id => $entityRows) {


                foreach ($entityRows as $row) {

                    $model = $this->employeeRepository->create();
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

    protected function deleteEntityFinish(array $ids, $table) {

        if ($table && $ids) {
            try {
                $this->countItemsDeleted += $this->_connection->delete(
                    $this->_connection->getTableName($table),
                    $this->_connection->quoteInto('entity_id IN (?)', $ids)
                );
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }
}
