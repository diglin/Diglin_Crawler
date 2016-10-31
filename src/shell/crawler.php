<?php
/**
 * Diglin GmbH - Switzerland
 *
 * @author      Sylvain Rayé <support at diglin.com>
 * @category    Diglin
 * @package     Diglin_Crawler
 * @copyright   Copyright (c) 2011-2016 Diglin (http://www.diglin.com)
 */

require_once(dirname(__FILE__) . '/abstract.php');

/**
 * Class Diglin_Crawler_shell
 */
class Diglin_Crawler_shell extends Mage_Shell_Abstract
{
    /**
     * Run script
     *
     * Warmup only configurable products, category and CMS pages
     */
    public function run()
    {
        ini_set('max_execution_time', 0); // Force to use via crawler helper to use a smaller max_execution_time
    
        $startTime = microtime(true);

        if ($this->getArg('debug')) {
            $urls = Mage::helper('diglin_crawler/cron')->getUrlQueue();
            print_r($urls);
            echo PHP_EOL;
            return;
        }

        if ($this->getArg('reset-url-queue')) {
            Mage::app()->removeCache(Diglin_Crawler_Helper_Cron::CRAWLER_URLS_CACHE_ID);
            Mage::helper('diglin_crawler/cron')->addUrlToCrawlerQueue('');
            echo 'Crawler Url queue reset' . PHP_EOL;
            return;
        }

        if ($this->getArg('username') && $this->getArg('password')) {
            $client = Mage::helper('diglin_crawler/cron')->getCrawlerClient();
            $client->setAuth($this->getArg('username'), $this->getArg('password'));
        }

        $verbose = false;
        if ($this->getArg('verbose') || $this->getArg('v')) {
            $verbose = true;
        }

        $skipmaxtime = false;
        if ($this->getArg('skip-max-execution-time')) {
            $skipmaxtime = true;
        }

        Mage::getModel('diglin_crawler/cron')->crawlUrls(true, $skipmaxtime, $verbose);

        $resultTime = microtime(true) - $startTime;
        echo "Warmup run successfully done in " . gmdate('H:i:s', $resultTime) . PHP_EOL;
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Crawl all urls of products, categories and cms pages

Not all urls will be crawled in one time, this script must be run several times during a period of time. For example, set a cron job to '0,10,20,30,40,50 * * * *' or use the parameter '--skip-max-execution-time' to execute it without limit, be aware that it can takes several hours to crawl all urls.
 
If the configuration is enabled and the Magento cron is correctly configured, you don't need to setup a cron with this script. The Diglin_Crawler Module will care of the execution

Usage:  php -f crawler.php -- [options]

  --username <USERNAME>        Username for basic authentification
  --password <PASS>            Password for basic authentification
  --skip-max-execution-time    Skip the maximum execution time
  --reset-url-queue            Reset the url queue, empty it and fill it with products, categories and cms pages urls - if parameter exists, crawler won't be executed
  -v|--verbose                 Verbose
  --debug                      Return the list of urls in queue
  help                         This help

USAGE;
    }
}

$shell = new Diglin_Crawler_shell();
$shell->run();
