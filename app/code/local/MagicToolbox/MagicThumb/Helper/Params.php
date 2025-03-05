<?php

class MagicToolbox_MagicThumb_Helper_Params extends Mage_Core_Helper_Abstract
{

    public function checkForOldModules()
    {
        static $oldModulesInstalled = null;
        if ($oldModulesInstalled === null) {
            $oldModulesInstalled = array();
            $modules = array(
                'magic360' => 'Magic 360',
                'magiczoom' => 'Magic Zoom',
                'magiczoomplus' => 'Magic Zoom Plus',
                'magicscroll' => 'Magic Scroll',
                'magicslideshow' => 'Magic Slideshow',
            );
            $inModules = "'".implode("_setup', '", array_keys($modules))."_setup'";
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_read');
            $table = $resource->getTableName('core/resource');
            $result = $connection->query("SELECT * FROM `{$table}` WHERE `code` IN ({$inModules})");
            if ($result) {
                while ($module = $result->fetch(PDO::FETCH_ASSOC)) {
                    if (version_compare($module['version'], '4.12.0', '<')) {
                        $key = str_replace('_setup', '', $module['code']);
                        if ($this->isModuleEnabled('MagicToolbox_'.str_replace(' ', '', $modules[$key]))) {
                            $oldModulesInstalled[] = array('name' => $modules[$key], 'version' => $module['version']);
                        }
                    }
                }
            }
        }
        return $oldModulesInstalled;
    }

    public function getFixedDefaultValues()
    {
        $defaultValues = self::getDefaultValues();
        foreach ($defaultValues as $platform => $platformData) {
            foreach ($platformData as $profile => $profileData) {
                foreach ($profileData as $param => $value) {
                    if ($param == 'enable-effect' || $param == 'include-headers-on-all-pages') {
                        $defaultValues[$platform][$profile][$param] = 'No';
                    }
                }
            }
        }
        return $defaultValues;
    }

    public function getSerializer() {
        static $serializer = null;

        if ($serializer === null) {
            if (class_exists('Zend_Serializer') && class_exists('Zend_Serializer_Adapter_PhpSerialize')) {
                $serializer = new Zend_Serializer;
            }
        }

        return $serializer;
    }

    public function getProfiles()
    {
        return array(
            'default' => 'Defaults',
            'product' => 'Product page',
            'category' => 'Category page',
            'newproductsblock' => 'New Products block',
            'recentlyviewedproductsblock' => 'Recently Viewed Products block'
        );
    }

    public function getDefaultValues()
    {
        return array(
            'desktop' => array(
                'product' => array(
                    'enable-effect' => 'Yes'
                ),
                'category' => array(
                    'enable-effect' => 'No',
                    'thumb-max-width' => '135',
                    'thumb-max-height' => '135'
                ),
                'newproductsblock' => array(
                    'enable-effect' => 'No',
                    'thumb-max-width' => '135',
                    'thumb-max-height' => '135'
                ),
                'recentlyviewedproductsblock' => array(
                    'enable-effect' => 'No',
                    'thumb-max-width' => '235',
                    'thumb-max-height' => '235'
                )
            ),
            'mobile' => array(
            )
        );
    }

    public function getParamsMap($block)
    {
        $blocks = array(
            'default' => array(
                'General' => array(
                    'include-headers-on-all-pages'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'square-images'
                ),
                'Expanded view' => array(
                    'expandEffect',
                    'expandSpeed',
                    'expandImageSize',
                    'expandTrigger',
                    'expandAlign',
                    'expandEasing',
                    'gallerySpeed'
                ),
                'Multiple images' => array(
                    'selector-max-width',
                    'selector-max-height',
                    'selectorTrigger',
                    'selectorEffect'
                ),
                'Title and Caption' => array(
                    'show-caption',
                    'captionPosition'
                ),
                'Miscellaneous' => array(
                    'link-to-product-page',
                    'keyboard',
                    'cssClass',
                    'rightClick',
                    'lazyLoad',
                    'autostart'
                ),
                'Buttons' => array(
                    'buttons',
                    'textBtnClose',
                    'textBtnNext',
                    'textBtnPrev'
                ),
                'Hint' => array(
                    'hint',
                    'textClickHint',
                    'textHoverHint'
                ),
                'Mobile' => array(
                    'slideMobileEffect',
                    'textClickHintForMobile'
                )
            ),
            'product' => array(
                'General' => array(
                    'enable-effect',
                    'template',
                    'magicscroll'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'square-images'
                ),
                'Expanded view' => array(
                    'expandEffect',
                    'expandSpeed',
                    'expandImageSize',
                    'expandTrigger',
                    'expandAlign',
                    'expandEasing',
                    'gallerySpeed'
                ),
                'Multiple images' => array(
                    'selector-max-width',
                    'selector-max-height',
                    'use-individual-titles',
                    'selectorTrigger',
                    'selectorEffect'
                ),
                'Title and Caption' => array(
                    'show-caption',
                    'captionPosition'
                ),
                'Miscellaneous' => array(
                    'option-associated-with-images',
                    'show-associated-product-images',
                    'load-associated-product-images',
                    'ignore-magento-css',
                    'keyboard',
                    'cssClass',
                    'rightClick',
                    'lazyLoad',
                    'autostart'
                ),
                'Buttons' => array(
                    'buttons',
                    'textBtnClose',
                    'textBtnNext',
                    'textBtnPrev'
                ),
                'Hint' => array(
                    'hint',
                    'textClickHint',
                    'textHoverHint'
                ),
                'Mobile' => array(
                    'slideMobileEffect',
                    'textClickHintForMobile'
                ),
                'Scroll' => array(
                    'width',
                    'height',
                    'mode',
                    'items',
                    'speed',
                    'autoplay',
                    'loop',
                    'step',
                    'arrows',
                    'pagination',
                    'easing',
                    'scrollOnWheel',
                    'lazy-load',
                    'scroll-extra-styles',
                    'show-image-title'
                )
            ),
            'category' => array(
                'General' => array(
                    'enable-effect'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'square-images'
                ),
                'Expanded view' => array(
                    'expandEffect',
                    'expandSpeed',
                    'expandImageSize',
                    'expandTrigger',
                    'expandAlign',
                    'expandEasing',
                    'gallerySpeed'
                ),
                'Multiple images' => array(
                    'selector-max-width',
                    'selector-max-height',
                    'show-selectors-on-category-page',
                    'selectorTrigger',
                    'selectorEffect'
                ),
                'Title and Caption' => array(
                    'show-caption',
                    'captionPosition'
                ),
                'Miscellaneous' => array(
                    'link-to-product-page',
                    'keyboard',
                    'cssClass',
                    'rightClick',
                    'lazyLoad',
                    'autostart'
                ),
                'Buttons' => array(
                    'buttons',
                    'textBtnClose',
                    'textBtnNext',
                    'textBtnPrev'
                ),
                'Hint' => array(
                    'hint',
                    'textClickHint',
                    'textHoverHint'
                ),
                'Mobile' => array(
                    'slideMobileEffect',
                    'textClickHintForMobile'
                )
            ),
            'newproductsblock' => array(
                'General' => array(
                    'enable-effect'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'square-images'
                ),
                'Expanded view' => array(
                    'expandEffect',
                    'expandSpeed',
                    'expandImageSize',
                    'expandTrigger',
                    'expandAlign',
                    'expandEasing',
                    'gallerySpeed'
                ),
                'Title and Caption' => array(
                    'show-caption',
                    'captionPosition'
                ),
                'Miscellaneous' => array(
                    'link-to-product-page',
                    'keyboard',
                    'cssClass',
                    'rightClick',
                    'lazyLoad',
                    'autostart'
                ),
                'Buttons' => array(
                    'buttons',
                    'textBtnClose',
                    'textBtnNext',
                    'textBtnPrev'
                ),
                'Hint' => array(
                    'hint',
                    'textClickHint',
                    'textHoverHint'
                ),
                'Mobile' => array(
                    'slideMobileEffect',
                    'textClickHintForMobile'
                )
            ),
            'recentlyviewedproductsblock' => array(
                'General' => array(
                    'enable-effect'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'square-images'
                ),
                'Expanded view' => array(
                    'expandEffect',
                    'expandSpeed',
                    'expandImageSize',
                    'expandTrigger',
                    'expandAlign',
                    'expandEasing',
                    'gallerySpeed'
                ),
                'Title and Caption' => array(
                    'show-caption',
                    'captionPosition'
                ),
                'Miscellaneous' => array(
                    'link-to-product-page',
                    'keyboard',
                    'cssClass',
                    'rightClick',
                    'lazyLoad',
                    'autostart'
                ),
                'Buttons' => array(
                    'buttons',
                    'textBtnClose',
                    'textBtnNext',
                    'textBtnPrev'
                ),
                'Hint' => array(
                    'hint',
                    'textClickHint',
                    'textHoverHint'
                ),
                'Mobile' => array(
                    'slideMobileEffect',
                    'textClickHintForMobile'
                )
            )
        );
        return $blocks[$block];
    }
}
