<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_Crawler
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

/**
 * Nexcess.net Turpentine Extension for Magento
 * Copyright (C) 2012  Nexcess.net L.L.C.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Class Diglin_Crawler_Model_Observer
 */
class Diglin_Crawler_Model_Observer
{
    /**
     * @var array
     */
    protected $_customerGroupIds = [];

    /**
     * Ban a specific product page from the cache
     *
     * Events:
     *     catalog_product_save_commit_after
     *
     * @param  Varien_Object $eventObject
     * @return null
     */
    public function catalogProductSaveCommitAfter($eventObject)
    {
        $helper = Mage::helper('diglin_crawler');

        if ($helper->isEnabled()) {

            $product = $eventObject->getProduct();
            $cronHelper = Mage::helper('diglin_crawler/cron');
            $cronHelper->addProductToCrawlerQueue($product);
            $this->_clearProductCache($product);

            foreach ($helper->getParentProducts($product) as $parentProduct) {
                $cronHelper->addProductToCrawlerQueue($parentProduct);
                $this->_clearProductCache($product);
            }
        }
    }

    /**
     * Ban a product page from the cache if it's stock status changed
     *
     * Events:
     *     cataloginventory_stock_item_save_after
     *
     * @param  Varien_Object $eventObject
     * @return null
     */
    public function cataloginventoryStockItemSaveAfter($eventObject)
    {
        $helper = Mage::helper('diglin_crawler');

        if ($helper->isEnabled()) {

            $item = $eventObject->getItem();

            if ($item->getStockStatusChangedAutomatically()
                || ($item->getOriginalInventoryQty() <= 0 && $item->getQty() > 0 && $item->getQtyCorrection() > 0)
            ) {
                $cronHelper = Mage::helper('diglin_crawler/cron');
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                $cronHelper->addProductToCrawlerQueue($product);
                $this->_clearProductCache($product);

                foreach ($helper->getParentProducts($product) as $parentProduct) {
                    $cronHelper->addProductToCrawlerQueue($parentProduct);
                    $this->_clearProductCache($parentProduct);
                }
            }
        }
    }

    /**
     * Ban a category page, and any subpages on save
     *
     * Events:
     *     catalog_category_save_commit_after
     *
     * @param  Varien_Object $eventObject
     * @return null
     */
    public function catalogCategorySaveCommitAfter($eventObject)
    {
        $helper = Mage::helper('diglin_crawler');

        if ($helper->isEnabled()) {
            $category = $eventObject->getCategory();
            $cronHelper = Mage::helper('diglin_crawler/cron');
            $cronHelper->addCategoryToCrawlerQueue($category);

            // @todo ban category cache page
        }
    }

    /**
     * Ban a specific CMS page from cache after edit
     *
     * Events:
     *     cms_page_save_commit_after
     *
     * @param  Varien_Object $eventObject
     * @return null
     */
    public function cmsPageSaveCommitAfter($eventObject)
    {
        $helper = Mage::helper('diglin_crawler');

        if ($helper->isEnabled()) {
            $pageId = $eventObject->getDataObject()->getIdentifier();

            $cronHelper = Mage::helper('diglin_crawler/cron');
            $cronHelper->addCmsPageToCrawlerQueue($pageId);

            // @todo ban cms cache
        }
    }

    /**
     * Ban a specific CMS page revision from cache after edit (enterprise edition only)
     * Events:
     *     enterprise_cms_revision_save_commit_after
     *
     * @param Varien_Object $eventObject
     * @return null
     */
    public function enterpriseCmsRevisionSaveCommitAfter($eventObject)
    {
        $helper = Mage::helper('diglin_crawler');

        if ($helper->isEnabled()) {
            $pageId = $eventObject->getDataObject()->getPageId();
            $page = Mage::getModel('cms/page')->load($pageId);

            // Don't do anything if the page isn't found.
            if (!$page) {
                return;
            }

            $pageIdentifier = $page->getIdentifier();
            $cronHelper = Mage::helper('diglin_crawler/cron');
            $cronHelper->addCmsPageToCrawlerQueue($pageIdentifier);

            // @todo ban cms cache
        }
    }

    /**
     * Queue all URLs
     *
     * @param Varien_Object $eventObject
     * @return null
     */
    public function queueAllUrls($eventObject = null)
    {
        $helper = Mage::helper('diglin_crawler');

        if ($helper->isEnabled()) {
            $cronHelper = Mage::helper('diglin_crawler/cron');
            $cronHelper->addUrlsToCrawlerQueue($cronHelper->getAllUrls());
        }
    }

    /**
     * @return array
     */
    protected function _getCustomerGroupIds()
    {
        if (empty($this->_customerGroupIds)) {

            $collection = Mage::getResourceModel('customer/group_collection');
            $ids = [];

            foreach ($collection->getItems() as $group) {
                $ids[] = $group->getId();
            }
            $this->_customerGroupIds = $ids;
        }

        return $this->_customerGroupIds;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _clearProductCache(Mage_Catalog_Model_Product $product)
    {
        if (class_exists('Diglin_Magento_Block_Catalog_Product_View')) {

            $origStore = Mage::app()->getStore();

            /* @var $store Mage_Core_Model_Store */
            foreach (Mage::app()->getStores() as $storeId => $store) {

                if (!$store->getIsActive()) {
                    continue;
                }

                Mage::app()->setCurrentStore($store);

                foreach ($this->_getCustomerGroupIds() as $customerGroupId) {
                    $key = [
                        'BLOCK_TPL',
                        $store->getCode(),
                        'template' => 'catalog/product/view.phtml',
                        $customerGroupId,
                        $product->getId()
                    ];

                    $key = array_values($key); // ignore array keys
                    $key = implode('|', $key);
                    $key = sha1($key);

                    $this->_banFromCache($key);
                }
            }

            Mage::app()->setCurrentStore($origStore);

        }

        Mage::dispatchEvent('diglin_crawler_clear_product_cache', ['product' => $product]);
    }

    /**
     * @param $id
     * @return bool
     */
    protected function _banFromCache($id)
    {
        return Mage::app()->getCacheInstance()->remove($id);
    }
}