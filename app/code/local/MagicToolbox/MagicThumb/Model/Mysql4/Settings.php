<?php

class MagicToolbox_MagicThumb_Model_Mysql4_Settings extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {

        $this->_init('magicthumb/settings', 'setting_id');

    }

}
