<?php
declare(strict_types=1);

class MagicToolbox_MagicThumb_Helper_Settings extends Mage_Core_Helper_Abstract
{
    /** @var MagicThumbModuleCoreClass|null */
    protected static $_toolCoreClass = null;

    /** @var MagicScrollModuleCoreClass|null */
    protected static $_scrollCoreClass = null;

    /** @var array<string, string> */
    protected static $_templates = [];

    /** @var array<string, string> */
    protected $_defaultTemplates = [];

    /** @var string */
    protected $_interface;

    /** @var string */
    protected $_theme;

    public function __construct()
    {
        $designPackage = Mage::getSingleton('core/design_package');
        $this->_interface = (string) $designPackage->getPackageName();
        $this->_theme = (string) $designPackage->getTheme('template');

        $this->_defaultTemplates = [
            'product.info.media' => 'catalog' . DS . 'product' . DS . 'view' . DS . 'media.phtml',
            'product_list' => 'catalog' . DS . 'product' . DS . 'list.phtml',
            'search_result_list' => 'catalog' . DS . 'product' . DS . 'list.phtml',
            'right.reports.product.viewed' => 'reports' . DS . 'product_viewed.phtml',
            'left.reports.product.viewed' => 'reports' . DS . 'product_viewed.phtml',
            'home.catalog.product.new' => 'catalog' . DS . 'product' . DS . 'new.phtml',
        ];
    }

    public function getBlockTemplate(string $blockName, string $template): string
    {
        if (!isset(self::$_templates[$blockName])) {
            $block = Mage::app()->getLayout()->getBlock($blockName);
            if ($block !== false && $block !== null) {
                $blockTemplate = $block->getTemplate();
                if (is_string($blockTemplate) && $blockTemplate !== '') {
                    self::$_templates[$blockName] = $blockTemplate;
                } else {
                    self::$_templates[$blockName] = $template;
                }
            } else {
                self::$_templates[$blockName] = $template;
            }
        }
        return self::$_templates[$blockName];
    }

    public function setOriginalTemplate(string $blockName, string $template): void
    {
        self::$_templates[$blockName] = $template;
    }

    public function getOriginalTemplate(string $blockName, string $default = ''): string
    {
        return self::$_templates[$blockName] ?? $default;
    }

    public function getTemplateFilename(string $blockName, string $defaultTemplate = ''): string
    {
        $template = self::$_templates[$blockName]
            ?? $this->_defaultTemplates[$blockName]
            ?? $defaultTemplate;

        // Mage::getSingleton returns object or throws, so no null expected
        $designPackage = Mage::getSingleton('core/design_package');

        try {
            $filename = $designPackage->getTemplateFilename($template);
        } catch (Exception $e) {
            // fallback to default template name if exception
            $filename = $template;
        }

        return (string) $filename;
    }

    public function loadTool(string $_profile = ''): MagicThumbModuleCoreClass
    {
        if (self::$_toolCoreClass === null) {
            $helper = Mage::helper('magicthumb/params');

            $coreClassPath = BP . str_replace('/', DS, '/app/code/local/MagicToolbox/MagicThumb/core/magicthumb.module.core.class.php');
            if (!file_exists($coreClassPath)) {
                throw new RuntimeException("MagicThumb core class file not found: $coreClassPath");
            }

            require_once $coreClassPath;

            self::$_toolCoreClass = new MagicThumbModuleCoreClass();

            $store = Mage::app()->getStore();

            $websiteId = (int)$store->getWebsiteId();
            $groupId = (int)$store->getGroupId();
            $storeId = (int)$store->getId();

            $designPackage = Mage::getSingleton('core/design_package');
            $interface = (string)$designPackage->getPackageName();
            $theme = (string)$designPackage->getTheme('template');

            $model = Mage::getModel('magicthumb/settings');
            $collection = $model ? $model->getCollection() : null;
            if ($collection === null) {
                throw new RuntimeException('Could not get MagicThumb settings collection');
            }

            $select = $collection->getSelect();
            $select->where('(website_id = ?) OR (website_id IS NULL)', $websiteId);
            $select->where('(group_id = ?) OR (group_id IS NULL)', $groupId);
            $select->where('(store_id = ?) OR (store_id IS NULL)', $storeId);
            $select->where('(package = ?) OR (package = \'\')', $interface);
            $select->where('(theme = ?) OR (theme = \'\')', $theme);
            $select->order(['theme DESC', 'package DESC', 'store_id DESC', 'group_id DESC', 'website_id DESC']);

            $settingsRaw = $collection->getFirstItem()->getValue();
            if (is_string($settingsRaw) && $settingsRaw !== '') {
                $settings = $this->safeUnserialize($settingsRaw);
                if (is_array($settings)) {
                    foreach (['desktop', 'mobile'] as $device) {
                        if (!empty($settings[$device]) && is_array($settings[$device])) {
                            foreach ($settings[$device] as $profile => $params) {
                                if (!is_array($params)) {
                                    continue;
                                }
                                foreach ($params as $id => $value) {
                                    if ($device === 'mobile') {
                                        self::$_toolCoreClass->params->setMobileValue($id, $value, $profile);
                                    } else {
                                        self::$_toolCoreClass->params->setValue($id, $value, $profile);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            foreach ($helper->getProfiles() as $id => $label) {
                $loadingMsg = $this->__('MagicThumb_LoadingMessage');
                if ($loadingMsg !== 'MagicThumb_LoadingMessage') {
                    self::$_toolCoreClass->params->setValue('loading-msg', $loadingMsg, $id);
                }
                $msg = $this->__('MagicThumb_Message');
                if ($msg !== 'MagicThumb_Message') {
                    self::$_toolCoreClass->params->setValue('message', $msg, $id);
                }
            }

            $loadScroll = false;
            $layout = Mage::app()->getLayout();
            if ($layout) {
                $headerBlock = $layout->getBlock('magicthumb_header');
                if ($headerBlock && method_exists($headerBlock, 'getPageType')) {
                    $loadScroll = ($headerBlock->getPageType() === 'product');
                }
            }

            if ($loadScroll && self::$_toolCoreClass->params->checkValue('magicscroll', 'yes', 'product')) {
                $scrollClassPath = BP . str_replace('/', DS, '/app/code/local/MagicToolbox/MagicThumb/core/magicscroll.module.core.class.php');
                if (file_exists($scrollClassPath)) {
                    require_once $scrollClassPath;
                    self::$_scrollCoreClass = new MagicScrollModuleCoreClass(false);
                    self::$_scrollCoreClass->params->appendParams(
                        self::$_toolCoreClass->params->getParams('product'),
                        'product-magicscroll-options'
                    );
                    $orientation = self::$_toolCoreClass->params->checkValue('template', ['left', 'right'], 'product') ? 'vertical' : 'horizontal';
                    self::$_scrollCoreClass->params->setValue('orientation', $orientation, 'product-magicscroll-options');

                    $helperClassName = Mage::getConfig()->getHelperClassName('magicscroll/settings');
                    if ($helperClassName === 'MagicToolbox_MagicScroll_Helper_Settings') {
                        Mage::helper('magicscroll/settings')->loadTool();
                    }
                }
            }

            $templateHelperClassPath = BP . str_replace('/', DS, '/app/code/local/MagicToolbox/MagicThumb/core/magictoolbox.templatehelper.class.php');
            if (file_exists($templateHelperClassPath)) {
                require_once $templateHelperClassPath;
                MagicToolboxTemplateHelperClass::setPath(
                    dirname(
                        Mage::getSingleton('core/design_package')->getTemplateFilename(
                            'magicthumb' . DS . 'templates' . DS . preg_replace('/[^a-zA-Z0-9_]/', '-', self::$_toolCoreClass->params->getValue('template', 'product')) . '.tpl.phtml'
                        )
                    )
                );
                MagicToolboxTemplateHelperClass::setOptions(self::$_toolCoreClass->params);
                MagicToolboxTemplateHelperClass::setExtension('phtml');
            }
        }

        if ($_profile !== '') {
            self::$_toolCoreClass->params->setProfile($_profile);
        }

        return self::$_toolCoreClass;
    }

    public function loadScroll(): ?MagicScrollModuleCoreClass
    {
        return self::$_scrollCoreClass;
    }

    /**
     * Safe unserialize wrapper
     *
     * @param string $data
     * @return mixed|null
     */
    protected function safeUnserialize(string $data)
    {
        if ($data === '') {
            return null;
        }

        if (class_exists(\Zend_Serializer::class)) {
            try {
                return \Zend_Serializer::unserialize($data);
            } catch (Exception $e) {
                // ignore, fallback
            }
        }

        try {
            $result = @unserialize($data);
            return $result === false && $data !== serialize(false) ? null : $result;
        } catch (Exception $e) {
            return null;
        }
    }
}
