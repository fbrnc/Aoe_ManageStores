<?php
/**
 * Aoe_ManageStores_Block_System_Store_Store.
 *
 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
 * @since 2012-02-29
 */
class Aoe_ManageStores_Block_System_Store_Grid extends Mage_Adminhtml_Block_System_Store_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aoe_managestores/managestores.phtml');
    }

    /**
     * Get table data
     *
     * @return array
     */
    public function getTableData()
    {
        $data = array();
        foreach (Mage::getModel('core/website')->getCollection() as $website) { /* @var $website Mage_Core_Model_Website */
            $data[$website->getId()]['_object'] = $website;
            $data[$website->getId()]['_count'] = 0;
            foreach ($website->getGroupCollection() as $storeGroup) { /* @var $storeGroup Mage_Core_Model_Store_Group */
                $data[$website->getId()]['_storeGroups'][$storeGroup->getId()]['_object'] = $storeGroup;
                $data[$website->getId()]['_storeGroups'][$storeGroup->getId()]['_count'] = 0;
                foreach ($storeGroup->getStoreCollection() as $store) { /* @var $store Mage_Core_Model_Store */
                    $data[$website->getId()]['_count']++;
                    $data[$website->getId()]['_storeGroups'][$storeGroup->getId()]['_count']++;
                    $data[$website->getId()]['_storeGroups'][$storeGroup->getId()]['_stores'][$store->getId()]['_object'] = $store;
                }
            }
        }

        // set default store groups and stores
        foreach ($data as $websiteId => $webSiteData) {
            $website = $webSiteData['_object']; /* @var $website Mage_Core_Model_Website */

            $defaultGroupId = $website->getDefaultGroupId();

            if ($defaultGroupId) {
                $defaultStoreGroup = $data[$websiteId]['_storeGroups'][$defaultGroupId]['_object']; /* @var $defaultStoreGroup Mage_Core_Model_Store_Group */
                $defaultStoreGroup->setData('is_default_in_website', true);
            }

            // show line for store groups that have no stores
            if ($data[$websiteId]['_count'] == 0) {
                $data[$websiteId]['_count'] = 1;
            }

            if (!isset($data[$websiteId]['_storeGroups'])) {
                continue;
            }

            foreach ($data[$websiteId]['_storeGroups'] as $storeGroupId => $storeGroupData) {
                $storeGroup = $storeGroupData['_object']; /* @var $storeGroup Mage_Core_Model_Store_Group */
                $defaultStoreId = $storeGroup->getDefaultStoreId();
                if ($defaultStoreId) {
                    $defaultStore = $data[$websiteId]['_storeGroups'][$storeGroupId]['_stores'][$defaultStoreId]['_object']; /* @var $defaultStore Mage_Core_Model_Store */
                    $defaultStore->setData('is_default_in_storegroup', true);
                }

                // show line for store groups that have no stores
                if ($data[$websiteId]['_storeGroups'][$storeGroupId]['_count'] == 0) {
                    $data[$websiteId]['_storeGroups'][$storeGroupId]['_count'] = 1;
                    $data[$websiteId]['_count']++;
                }
            }
        }

        return $data;
    }

    /**
     * Render website cell
     *
     * @param  Mage_Core_Model_Website $website
     * @return string
     */
    public function renderWebsiteCell(Mage_Core_Model_Website $website)
    {
        $result = '<a title="Id: '.$website->getId().'" href="'.$this->getUrl('*/*/editWebsite', array('website_id'=>$website->getWebsiteId())).'">' . $website->getName() . '</a>';
        if ($website->getIsDefault()) {
            $result = '<strong>'.$result.'</strong>';
        }
        $result .= ' <br /><span class="additional-info">(' . $this->__('Code') . ': ' . $website->getCode().')</span>';

        return $result;
    }

    /**
     * Render store group cell
     *
     * @param  Mage_Core_Model_Store_Group $storeGroup
     * @return string
     */
    public function renderStoreGroupCell(Mage_Core_Model_Store_Group $storeGroup)
    {
        $result = '<a title="Id: '.$storeGroup->getId().'" href="'.$this->getUrl('*/*/editGroup', array('group_id'=>$storeGroup->getGroupId())).'">' . $storeGroup->getName() . '</a>';
        if ($storeGroup->getData('is_default_in_website')) {
            $result = '<strong>'.$result.'</strong>';
        }

        $rootCategory = Mage::getModel('catalog/category')->load($storeGroup->getRootCategoryId());

        $result .= ' <br /><span class="additional-info">(' . $this->__('Root Category') . ': ' . $rootCategory->getName().')</span>';

        return $result;
    }

    /**
     * Render store cell
     *
     * @param  Mage_Core_Model_Store $store
     * @return string
     */
    public function renderStoreCell(Mage_Core_Model_Store $store)
    {
        $result = '<a title="Id: '.$store->getId().'" href="'.$this->getUrl('*/*/editStore', array('store_id'=>$store->getStoreId())).'">' . $store->getName() . '</a>';
        if (!$store->getIsActive()) {
            $result = '<strike>'.$result.'</strike>';
        }
        if ($store->getData('is_default_in_storegroup')) {
            $result = '<strong>'.$result.'</strong>';
        }
        $result .= ' <br /><span class="additional-info">(' . $this->__('Code') . ': ' . $store->getCode().')</span>';

        return $result;
    }

}
