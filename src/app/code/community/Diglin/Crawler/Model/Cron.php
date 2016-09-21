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
 * Class Diglin_Crawler_Model_Cron
 */
class Diglin_Crawler_Model_Cron extends Varien_Event_Observer
{

    /**
     * Max time to crawl URLs if max_execution_time is 0 (unlimited) in seconds
     *
     * @var int
     */
    const MAX_CRAWL_TIME = 300;

    /**
     * Amount of time of execution time to leave for other cron processes
     *
     * @var int
     */
    const EXEC_TIME_BUFFER = 15;

    /**
     * Crawl available URLs in the queue until we get close to max_execution_time
     * (up to MAX_CRAWL_TIME)
     *
     * @param bool $force
     * @param bool $skipMaxExecutionTime
     * @param bool $verbose
     */
    public function crawlUrls($force = false, $skipMaxExecutionTime = false, $verbose = false)
    {
        $helper = Mage::helper('diglin_crawler');
        if ($helper->isEnabled() || $force) {

            $cronHelper = Mage::helper('diglin_crawler/cron');

            $maxRunTime = $cronHelper->getAllowedRunTime();
            if ($maxRunTime === 0) {
                $maxRunTime = self::MAX_CRAWL_TIME;
            }

            // just in case we have a silly short max_execution_time
            $maxRunTime = abs($maxRunTime - self::EXEC_TIME_BUFFER);

            while ((($cronHelper->getRunTime() < $maxRunTime) || $skipMaxExecutionTime) && $url = $cronHelper->getNextUrl()) {
                if (!$this->_crawlUrl($url, $verbose)) {
                    Mage::helper('diglin_crawler')->logWarn('Failed to crawl URL: %s', $url, $verbose);
                }
            }
        }
    }

    /**
     * Request a single URL, returns whether the request was successful or not
     *
     * @param $url
     * @param bool $verbose
     * @return bool
     */
    protected function _crawlUrl($url, $verbose = false)
    {
        $client = Mage::helper('diglin_crawler/cron')->getCrawlerClient();
        $client->setUri($url);

        Mage::helper('diglin_crawler')->logDebug('Crawling URL: %s', $url, $verbose);

        try {
            $response = $client->request();
        } catch (Exception $e) {
            Mage::helper('diglin_crawler')->logWarn('Error crawling URL (%s): %s', $url, $e->getMessage(), $verbose);

            return false;
        }

        return $response->isSuccessful();
    }
}
