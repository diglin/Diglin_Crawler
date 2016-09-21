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
 * Class Diglin_Crawler_Helper_Data
 */
class Diglin_Crawler_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CFG_ENABLED = 'diglin_crawler/config/enabled';
    const LOG_FILE = 'crawler.log';

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return Mage::getConfig()->getModuleConfig('Diglin_Crawler')->version;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::CFG_ENABLED);
    }

    /**
     * Get parent products of a configurable or group product
     *
     * @param  Mage_Catalog_Model_Product $childProduct
     * @return array
     */
    public function getParentProducts($childProduct)
    {
        $parentProducts = array();
        foreach (array('configurable', 'grouped') as $pType) {
            $ids = Mage::getModel('catalog/product_type_' . $pType)->getParentIdsByChild($childProduct->getId());
            foreach ($ids as $parentId) {
                $parentProducts[] = Mage::getModel('catalog/product')->load($parentId);
            }
        }

        return $parentProducts;
    }

    /**
     * Logs errors
     *
     * @param $message
     * @return string
     */
    public function logError($message, $output = false)
    {
        if (func_num_args() > 1) {
            $message = $this->_prepareLogMessage(func_get_args());
        }

        return $this->_log(Zend_Log::ERR, $message, $output);
    }

    /**
     * Logs warnings
     *
     * @param $message
     * @return string
     */
    public function logWarn($message, $output = false)
    {
        if (func_num_args() > 1) {
            $message = $this->_prepareLogMessage(func_get_args());
        }

        return $this->_log(Zend_Log::WARN, $message, $output);
    }

    /**
     * Logs notices
     *
     * @param $message
     * @return string
     */
    public function logNotice($message, $output = false)
    {
        if (func_num_args() > 1) {
            $message = $this->_prepareLogMessage(func_get_args());
        }

        return $this->_log(Zend_Log::NOTICE, $message, $output);
    }

    /**
     * Logs info.
     *
     * @param $message
     * @return string
     */
    public function logInfo($message, $output = false)
    {
        if (func_num_args() > 1) {
            $message = $this->_prepareLogMessage(func_get_args());
        }

        return $this->_log(Zend_Log::INFO, $message, $output);
    }

    /**
     * Logs debug.
     *
     * @param $message
     * @return string
     */
    public function logDebug($message, $output = false)
    {
        if (func_num_args() > 1) {
            $message = $this->_prepareLogMessage(func_get_args());
        }

        return $this->_log(Zend_Log::DEBUG, $message, $output);
    }

    /**
     * Prepares advanced log message.
     *
     * @param array $args
     * @return string
     */
    protected function _prepareLogMessage(array $args)
    {
        $pattern = $args[0];
        $substitutes = array_slice($args, 1);

        if (!$this->_validatePattern($pattern, $substitutes)) {
            return $pattern;
        }

        return vsprintf($pattern, $substitutes);
    }

    /**
     * Validates string and attributes for substitution as per sprintf function.
     *
     * NOTE: this could be implemented as shown at
     * http://stackoverflow.com/questions/2053664/how-to-check-that-vsprintf-has-the-correct-number-of-arguments-before-running
     * although IMHO it's too time consuming to validate the patterns.
     *
     * @param string $pattern
     * @param array $arguments
     * @return bool
     */
    protected function _validatePattern($pattern, $arguments)
    {
        return true;
    }

    /**
     * Log a message through Magento's logging facility
     *
     * @param  int $level
     * @param  string $message
     * @param  bool $output
     * @return string
     */
    protected function _log($level, $message, $output = false)
    {
        $message = 'DIGLIN_CRAWLER: ' . $message;

        if ($output) {
            echo $message . PHP_EOL;
        }

        Mage::log($message, $level, self::LOG_FILE);

        return $message;
    }
}