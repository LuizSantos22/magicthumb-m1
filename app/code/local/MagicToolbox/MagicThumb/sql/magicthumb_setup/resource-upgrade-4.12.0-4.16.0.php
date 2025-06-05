<?php
/** @var $installer MagicToolbox_MagicThumb_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$tableName = $installer->getTable('magicthumb/settings');

$paramsHelper = Mage::helper('magicthumb/params');
$serializer = $paramsHelper->getSerializer();

$oldModulesInstalled = $paramsHelper->checkForOldModules();

if (!empty($oldModulesInstalled)) {
    if ($connection->isTableExists($tableName)) {
        $select = $connection->select()
            ->from($tableName);
        $rows = $connection->fetchAll($select);

        foreach ($rows as $row) {
            if (!empty($row['value'])) {
                try {
                    $settings = $serializer->unserialize($row['value']);
                } catch (Exception $e) {
                    Mage::logException($e);
                    continue;
                }

                foreach ($settings as $platform => &$platformData) {
                    foreach ($platformData as $profile => &$profileData) {
                        foreach ($profileData as $param => &$value) {
                            if (in_array($param, ['enable-effect', 'include-headers-on-all-pages'])) {
                                $value = 'No';
                            }
                        }
                    }
                }

                $newValue = $serializer->serialize($settings);
                $connection->update($tableName, ['value' => $newValue], ['setting_id = ?' => $row['setting_id']]);
            }
        }
    }
}

// Ensure entity is installed
$attribute = $installer->getAttribute('catalog_product', 'product_videos');
if (!$attribute) {
    $installer->installEntities();
}

$installer->endSetup();
