<?php
/** @var $installer MagicToolbox_MagicThumb_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

try {
    $attribute = $installer->getAttribute('catalog_product', 'product_videos');
    if (!$attribute || !is_array($attribute)) {
        $installer->installEntities();
    }
} catch (Exception $e) {
    Mage::logException($e); // log exception if attribute retrieval fails
}

$installer->endSetup();
