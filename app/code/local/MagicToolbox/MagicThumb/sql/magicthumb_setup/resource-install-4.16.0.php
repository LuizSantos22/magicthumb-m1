<?php
/** @var $installer MagicToolbox_MagicThumb_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$tableName = $installer->getTable('magicthumb/settings');

$paramsHelper = Mage::helper('magicthumb/params');

$oldModulesInstalled = $paramsHelper->checkForOldModules();
$mtDefaultValues = empty($oldModulesInstalled)
    ? $paramsHelper->getDefaultValues()
    : $paramsHelper->getFixedDefaultValues();

$mtDefaultValuesSerialized = $paramsHelper->getSerializer()->serialize($mtDefaultValues);

// Drop old table if it exists
if ($connection->isTableExists($tableName)) {
    $connection->dropTable($tableName);
}

// Create new table
$table = $connection->newTable($tableName)
    ->addColumn('setting_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
        'unsigned' => true,
    ], 'ID')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, ['nullable' => true], 'Website ID')
    ->addColumn('group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, ['nullable' => true], 'Group ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, ['nullable' => true], 'Store ID')
    ->addColumn('package', Varien_Db_Ddl_Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Package')
    ->addColumn('theme', Varien_Db_Ddl_Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Theme')
    ->addColumn('last_edit_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, ['nullable' => true], 'Last Edit Time')
    ->addColumn('custom_settings_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Title')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', ['nullable' => true], 'Serialized Value')
    ->setComment('Magic Thumb Settings');

$connection->createTable($table);

// Insert default settings row
$connection->insert($tableName, [
    'website_id' => new Zend_Db_Expr('NULL'),
    'group_id'   => new Zend_Db_Expr('NULL'),
    'store_id'   => new Zend_Db_Expr('NULL'),
    'package'    => '',
    'theme'      => '',
    'last_edit_time' => new Zend_Db_Expr('NULL'),
    'custom_settings_title' => 'Edit Magic Thumb default settings',
    'value'      => $mtDefaultValuesSerialized,
]);

// Handle entity attribute
$attribute = $installer->getAttribute('catalog_product', 'product_videos');
if (!$attribute) {
    $installer->installEntities();
}

$installer->endSetup();
