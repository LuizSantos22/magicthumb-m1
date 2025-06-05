<?php

class MagicToolbox_MagicThumb_Helper_Params extends Mage_Core_Helper_Abstract
{
    public function checkForOldModules(): array
    {
        static $oldModulesInstalled = [];

        if (empty($oldModulesInstalled)) {
            $modules = [
                'magic360' => 'Magic 360',
                'magiczoom' => 'Magic Zoom',
                'magiczoomplus' => 'Magic Zoom Plus',
                'magicscroll' => 'Magic Scroll',
                'magicslideshow' => 'Magic Slideshow',
            ];

            $inModules = "'" . implode("_setup', '", array_keys($modules)) . "_setup'";
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_read');
            $table = $resource->getTableName('core/resource');

            $query = "SELECT * FROM `{$table}` WHERE `code` IN ({$inModules})";
            $result = $connection->query($query);

            if ($result) {
                foreach ($result as $module) {
                    if (version_compare($module['version'], '4.12.0', '<')) {
                        $key = str_replace('_setup', '', $module['code']);
                        $moduleName = 'MagicToolbox_' . str_replace(' ', '', $modules[$key]);
                        if (Mage::helper('core')->isModuleEnabled($moduleName)) {
                            $oldModulesInstalled[] = [
                                'name' => $modules[$key],
                                'version' => $module['version']
                            ];
                        }
                    }
                }
            }
        }

        return $oldModulesInstalled;
    }

    public function getFixedDefaultValues(): array
    {
        $defaultValues = self::getDefaultValues();
        foreach ($defaultValues as $platform => $platformData) {
            foreach ($platformData as $profile => $profileData) {
                foreach ($profileData as $param => $value) {
                    if (in_array($param, ['enable-effect', 'include-headers-on-all-pages'], true)) {
                        $defaultValues[$platform][$profile][$param] = 'No';
                    }
                }
            }
        }
        return $defaultValues;
    }

    public function getSerializer(): ?Zend_Serializer_Adapter_PhpSerialize
    {
        static $serializer = null;

        if ($serializer === null && class_exists('Zend_Serializer_Adapter_PhpSerialize')) {
            $serializer = new Zend_Serializer_Adapter_PhpSerialize();
        }

        return $serializer;
    }

    public function getProfiles(): array
    {
        return [
            'default' => 'Defaults',
            'product' => 'Product page',
            'category' => 'Category page',
            'newproductsblock' => 'New Products block',
            'recentlyviewedproductsblock' => 'Recently Viewed Products block',
        ];
    }

    public static function getDefaultValues(): array
    {
        return [
            'desktop' => [
                'product' => ['enable-effect' => 'Yes'],
                'category' => [
                    'enable-effect' => 'No',
                    'thumb-max-width' => '135',
                    'thumb-max-height' => '135',
                ],
                'newproductsblock' => [
                    'enable-effect' => 'No',
                    'thumb-max-width' => '135',
                    'thumb-max-height' => '135',
                ],
                'recentlyviewedproductsblock' => [
                    'enable-effect' => 'No',
                    'thumb-max-width' => '235',
                    'thumb-max-height' => '235',
                ],
            ],
            'mobile' => [],
        ];
    }

    public function getParamsMap($block): array
    {
        $blocks = $this->getAllParamsMap();
        return isset($blocks[$block]) ? $blocks[$block] : [];
    }

    private function getAllParamsMap(): array
    {
        // (Your full $blocks array content goes here — unchanged from your original version)
        return [ /* ... same content from your original getParamsMap method ... */ ];
    }
}
