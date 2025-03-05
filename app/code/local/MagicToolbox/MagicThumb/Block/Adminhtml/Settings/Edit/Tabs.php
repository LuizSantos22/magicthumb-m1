<?php

class MagicToolbox_MagicThumb_Block_Adminhtml_Settings_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {

        parent::__construct();

        $this->setId('magicthumb_config_tabs');
        $this->setDestElementId('edit_form');//this should be same as the form id
        $this->setTitle('<span style="visibility: hidden">'.Mage::helper('magicthumb')->__('Supported blocks:').'</span>');

    }

    protected function _beforeToHtml()
    {

        $blocks = Mage::helper('magicthumb/params')->getProfiles();
        $activeTab = $this->getRequest()->getParam('tab', 'product');

        foreach ($blocks as $id => $label) {
            $this->addTab($id, array(
                'label'     => Mage::helper('magicthumb')->__($label),
                'title'     => Mage::helper('magicthumb')->__($label.' settings'),
                'content'   => $this->getLayout()->createBlock('magicthumb/adminhtml_settings_edit_tab_form', 'magicthumb_'.$id.'_settings_block')->toHtml(),
                'active'    => ($id == $activeTab) ? true : false
            ));
        }

        //NOTE: promo section for Sirv extension
        $this->addTab('promo', array(
            'label'     => Mage::helper('magicthumb')->__('CDN and Image Processing'),
            'title'     => Mage::helper('magicthumb')->__('CDN and Image Processing'),
            'content'   => $this->getLayout()->createBlock(
                'magicthumb/adminhtml_settings_edit_tab_promo',
                'magicthumb_promo_block'
            )->toHtml(),
            'active'    => ('promo' == $activeTab)
        ));

        return parent::_beforeToHtml();
    }
}
