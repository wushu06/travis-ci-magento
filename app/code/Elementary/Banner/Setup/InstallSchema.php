<?php

namespace Elementary\Banner\Setup;

use Elementary\Banner\Api\Data;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install Schema
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->_setupBannerTable($setup);
        $this->_setupSlideTable($setup);
        $this->_setupBannerSlideTable($setup);
        $setup->endSetup();
    }

    /**
     * This method will setup the table for the banner entities
     *
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    protected function _setupBannerTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(Data\BannerInterface::TABLE)
        );

        $table->addColumn(Data\BannerInterface::BANNER_ID,Table::TYPE_INTEGER,null, [
            'identity' => true,
            'nullable' => false,
            'primary'  => true
        ],'Banner ID');

        $table->addColumn(Data\BannerInterface::IDENTIFIER, Table::TYPE_TEXT, 255, [
            'nullable' => false
        ],'Identifier');

        $table->addColumn(Data\BannerInterface::STATUS,Table::TYPE_INTEGER,null, [
            'default' => 1
        ], 'Status');

        $setup->getConnection()->createTable($table);
    }

    /**
     * This method will setup the slide table
     *
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    protected function _setupSlideTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(Data\SlideInterface::TABLE)
        );

        $table->addColumn(Data\SlideInterface::SLIDE_ID,Table::TYPE_INTEGER,null, [
            'identity' => true,
            'nullable' => false,
            'primary'  => true
        ],'Slide ID');

        $table->addColumn(Data\SlideInterface::TITLE, Table::TYPE_TEXT, null, [
            'nullable' => false
        ],'Title');

        $table->addColumn(Data\SlideInterface::CONTENT, Table::TYPE_TEXT, null, [
            'nullable' => true
        ],'Content');

        $table->addColumn(Data\SlideInterface::URL, Table::TYPE_TEXT, null, [
            'nullable' => false
        ],'Url');

        $table->addColumn(Data\SlideInterface::URL_TITLE, Table::TYPE_TEXT, null, [
            'nullable' => true
        ],'Url Title');

        $table->addColumn(Data\SlideInterface::SHOW_BUTTON, Table::TYPE_INTEGER, null, [
            'nullable' => true,
            'default'  => 1
        ],'Show Button');

        $table->addColumn(Data\SlideInterface::IMAGE, Table::TYPE_TEXT, null, [
            'nullable' => false
        ],'Image');

        $table->addColumn(Data\SlideInterface::BUTTON_TITLE, Table::TYPE_TEXT, null, [
            'nullable' => true
        ],'Url Title');

        $table->addColumn(Data\SlideInterface::CREATED_AT,Table::TYPE_TIMESTAMP,null, [
            'nullable' => false,
            'default'  => Table::TIMESTAMP_INIT
        ],'Created At');

        $table->addColumn(Data\SlideInterface::UPDATED_AT,Table::TYPE_TIMESTAMP,null, [
            'nullable' => false,
            'default'  => Table::TIMESTAMP_INIT_UPDATE,
        ],'Updated At');

        $table->addColumn(Data\SlideInterface::START_DATE,Table::TYPE_DATETIME,null, [
            'nullable' => true,
        ],'Start Date');

        $table->addColumn(Data\SlideInterface::FINISH_DATE,Table::TYPE_DATETIME,null, [
            'nullable' => true,
        ],'Finish Date');

        $table->addColumn(Data\SlideInterface::STATUS,Table::TYPE_INTEGER,null, [
            'default' => 1
        ], 'Status');

        $setup->getConnection()->createTable($table);
    }

    /**
     * This method will setup the link table to link a slide to a banner
     *
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    protected function _setupBannerSlideTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(Data\BannerInterface::TABLE_BANNER_SLIDE)
        );
        
        $table->addColumn(Data\BannerInterface::BANNER_ID, Table::TYPE_INTEGER, null, [
            'nullable' => false,
            'primary'  => true
        ], 'Banner ID');

        $table->addColumn(Data\SlideInterface::SLIDE_ID, Table::TYPE_INTEGER, null, [
            'nullable' => false,
            'primary'  => true
        ], 'Slide ID');

        $table->addColumn(Data\BannerInterface::POSITION, Table::TYPE_TEXT, 11, [
            'nullable' => true,
            'default'  => 0
        ],'Position');
        
        $table->addIndex(
            $setup->getIdxName(Data\BannerInterface::TABLE_BANNER_SLIDE, [
                'slide_id'
            ]),
            ['slide_id']
        );

        $table->addForeignKey(
            $setup->getFkName(
                Data\BannerInterface::TABLE_BANNER_SLIDE,
                Data\BannerInterface::BANNER_ID,
                Data\BannerInterface::TABLE,
                Data\BannerInterface::BANNER_ID
            ),
            Data\BannerInterface::BANNER_ID,
            $setup->getTable(Data\BannerInterface::TABLE),
            Data\BannerInterface::BANNER_ID,
            Table::ACTION_CASCADE
        );

       $table->addForeignKey(
           $setup->getFkName(
               Data\BannerInterface::TABLE_BANNER_SLIDE,
               Data\SlideInterface::SLIDE_ID,
               Data\SlideInterface::TABLE,
               Data\SlideInterface::SLIDE_ID
           ),
           Data\SlideInterface::SLIDE_ID,
           $setup->getTable(Data\SlideInterface::TABLE),
           Data\SlideInterface::SLIDE_ID,
           Table::ACTION_CASCADE
       );

       $setup->getConnection()->createTable($table);
    }
}
