<?php

/* @var $installer MagicToolbox_MagicThumb_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


$attribute = $installer->getAttribute('catalog_product', 'product_videos');
if (!$attribute) {
    $installer->installEntities();
}

$installer->endSetup();
