<?php

class MagicToolbox_MagicThumb_MagicthumbController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {

        $this->_title($this->__('Magic Thumb&#8482; Settings'));
        $this->loadLayout()->_setActiveMenu('magictoolbox/magicthumb')->renderLayout();

    }

    public function addAction()
    {

        if ($data = $this->getRequest()->getPost()) {

            if (empty($data['store_views']) && empty($data['design'])) {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magicthumb')->__('You already have default settings!'));
                $this->_redirect('*/*/');
                return;
            }

            $websiteId = null;
            $groupId = null;
            $storeId = null;
            if (empty($data['store_views'])) {
                $data['store_views'] = '';
            } else {
                list($websiteId, $groupId, $storeId) = explode('/', $data['store_views']);
            }

            $package = null;
            $theme = null;
            if (empty($data['design'])) {
                $data['design'] = '';
            } else {
                list($package, $theme) = explode('/', $data['design']);
            }

            $model = Mage::getModel('magicthumb/settings');
            $collection = $model->getCollection();

            $collection->getSelect()->/*columns('custom_settings_title')->*/
                where(empty($websiteId) ? 'website_id IS NULL' : 'website_id = ?', empty($websiteId) ? null : (int)$websiteId)->
                where(empty($groupId)   ? 'group_id IS NULL'   : 'group_id = ?',   empty($groupId)   ? null : (int)$groupId)->
                where(empty($storeId)   ? 'store_id IS NULL'   : 'store_id = ?',   empty($storeId)   ? null : (int)$storeId)->
                where('package = ?', empty($package) ? '' : $package)->
                where('theme = ?', empty($theme) ? '' : $theme);

            if ($collection->getSize()) {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magicthumb')->__('The settings already exists!'));
                $this->_redirect('*/*/');
                return;
            }

            $customSettingsTitle = array();

            if (empty($data['store_views'])) {
                $customSettingsTitle[] = 'All Store Views';
            } else {
                if (!empty($websiteId)) {
                    $model->setWebsite_id($websiteId);
                    $website = Mage::app()->getWebsite($websiteId);
                    $customSettingsTitle[] = $website->getName();
                }
                if (!empty($groupId)) {
                    $model->setGroup_id($groupId);
                    $group = $website->getGroups();
                    $group = $group[$groupId];
                    if (!$group instanceof Mage_Core_Model_Store_Group) {
                        $group = Mage::app()->getGroup($group);
                    }
                    $customSettingsTitle[] = $group->getName();
                }
                if (!empty($storeId)) {
                    $model->setStore_id($storeId);
                    $store = $group->getStores();
                    $store = $store[$storeId];
                    $customSettingsTitle[] = $store->getName();
                }
            }

            if (empty($data['design'])) {
                $customSettingsTitle[] = 'All Designs';
            } else {
                if (empty($theme)) {
                    $model->setPackage($package);
                    $customSettingsTitle[] = $package.' package';
                } else {
                    $model->setPackage($package);
                    $model->setTheme($theme);
                    $customSettingsTitle[] = $package.'/'.$theme.' theme';
                }
            }

            $customSettingsTitle = 'Settings for '.implode(' => ', $customSettingsTitle);
            $model->setCustom_settings_title($customSettingsTitle);

            $oldModulesInstalled = Mage::helper('magicthumb/params')->checkForOldModules();
            if (empty($oldModulesInstalled)) {
                $defaultValues = Mage::helper('magicthumb/params')->getDefaultValues();
            } else {
                $defaultValues = Mage::helper('magicthumb/params')->getFixedDefaultValues();
            }
            //NOTE: quotes need to be escaped
            $defaultValues = Mage::helper('magicthumb/settings')->getSerializer()->serialize($defaultValues);

            $model->setValue($defaultValues);

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magicthumb')->__('Settings was successfully added'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
            } catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
            }

        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magicthumb')->__('Unable to add settings'));
        }
        $this->_redirect('*/*/');

    }

    public function deleteAction()
    {

        $id = $this->getRequest()->getParam('id');
        if ($id > 0) {
            try {
                $model = Mage::getModel('magicthumb/settings')->load($id);
                $isDefaultSettings =
                    $model->getWebsite_id() == NULL &&
                    $model->getGroup_id() == NULL &&
                    $model->getStore_id() == NULL &&
                    $model->getPackage() == '' &&
                    $model->getTheme() == '';
                if ($isDefaultSettings) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('You can not delete default settings!'));
                } else {
                    $model->delete();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Settings was successfully deleted'));
                }
            } catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');

    }

    public function massDeleteAction()
    {

        $ids = $this->getRequest()->getParam('massactionId');
        $alert = 0;
        if (is_array($ids)) {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('magicthumb/settings')->load($id);
                    $isDefaultSettings =
                        $model->getWebsite_id() == NULL &&
                        $model->getGroup_id() == NULL &&
                        $model->getStore_id() == NULL &&
                        $model->getPackage() == '' &&
                        $model->getTheme() == '';
                    if ($isDefaultSettings) {
                        $alert = 1;
                    } else {
                        $model->delete();
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d row(s) were successfully deleted', count($ids)-$alert)
                );
                if ($alert) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('You can not delete default settings!')
                    );
                }
            } catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select rows'));
        }
        $this->_redirect('*/*/');

    }

    public function editAction()
    {

        $id = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('magicthumb/settings')->load($id);
        if ($model->getId()) {
            Mage::register('magicthumb_model_data', $model);

            $oldModulesInstalled = Mage::helper('magicthumb/params')->checkForOldModules();
            if (!empty($oldModulesInstalled)) {
                $message = 'You have installed '.
                    $oldModulesInstalled[0]['name'].' v'.$oldModulesInstalled[0]['version'].'. '.
                    'Pease update it to the latest version to work correctly with this version of the Magic Thumb module.';
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magicthumb')->__($message));
            }

            $this->_title($this->__('Magic Thumb&#8482; Settings'));
            $this->loadLayout();
            $this->_setActiveMenu('magictoolbox/magicthumb');
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magicthumb')->__('Settings does not exist'));
            $this->_redirect('*/*/');
        }

    }

    public function saveAction()
    {

        if ($post = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('magicthumb/settings');


            /*
            foreach ($post['magicthumb'] as $block => $params) {
                if (is_array($params))
                foreach ($params  as $paramId => $value) {
                    if (isset($post['magicthumb-defaults'][$block][$paramId])) {
                        unset($post['magicthumb'][$block][$paramId]);
                    }
                }
            }
            */

            $data = array();

            $postSettings = $post['magicthumb'];
            $oldModulesInstalled = Mage::helper('magicthumb/params')->checkForOldModules();
            if (!empty($oldModulesInstalled)) {
                foreach ($postSettings as $platform => $platformData) {
                    foreach ($platformData as $profile => $profileData) {
                        foreach ($profileData as $param => $value) {
                            if ($param == 'enable-effect' || $param == 'include-headers-on-all-pages') {
                                $postSettings[$platform][$profile][$param] = 'No';
                            }
                        }
                    }
                }
            }

            $data['value'] = Mage::helper('magicthumb/settings')->getSerializer()->serialize($postSettings);
            $data['last_edit_time'] = now();
            $model->setData($data)->setId($id);
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('magicthumb')->__('Settings was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array(
                        'id'        => $id,
                        '_current'  => true,
                        'back'      => null
                    ));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($post);
                $this->_redirect('*/*/edit', array('id' => $id));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magicthumb')->__('Unable to find settings to save'));
        $this->_redirect('*/*/');

    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        $this->getResponse()->setBody($response->toJson());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/magictoolbox/magicthumb');
    }

}
