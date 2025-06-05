<?php
/** @var $installer MagicToolbox_MagicThumb_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$tableName = $installer->getTable('magicthumb/settings');

$paramsHelper = Mage::helper('magicthumb/params');
$serializer = $paramsHelper->getSerializer();

$oldModulesInstalled = $paramsHelper->checkForOldModules();

if (!empty($oldModulesInstalled) && $connection->isTableExists($tableName)) {
    $rows = $connection->fetchAll(
        $connection->select()->from($tableName)
    );

    foreach ($rows as $row) {
        if (!empty($row['value'])) {
            try {
                $settings = $serializer->unserialize($row['value']);
            } catch (Exception $e) {
                Mage::logException($e);
                continue;
            }

            if (is_array($settings)) {
                foreach ($settings as $platform => $platformData) {
                    if (!is_array($platformData)) continue;

                    foreach ($platformData as $profile => $profileData) {
                        if (!is_array($profileData)) continue;

                        foreach ($profileData as $param => $value) {
                            if (in_array($param, ['enable-effect', 'include-headers-on-all-pages'], true)) {
                                $settings[$platform][$profile][$param] = 'No';
                            }
                        }
                    }
                }

                $newValue = $serializer->serialize($settings);
                $connection->update(
                    $tableName,
                    ['value' => $newValue],
                    ['setting_id = ?' => (int)$row['setting_id']]
                );
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
