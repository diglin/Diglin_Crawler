# Diglin_Crawler - Magento 1.x Module

## Description

The Diglin_Crawler Magento 1.x extension allows to crawl all products, categories and cms pages to warmup the cache for example. It's based on the Nexcess Turpentine warmup cache without dependencies with Varnish and with some additional features

## Features

- Add cms, products or categories urls to queue as soon as they are saved in backend
- Run all the day during 5 minutes each 10 minutes to reduce server resources consumption
- A shell script is provided under the folder `shell/crawler.php` with possible options. Use `php shell/crawler.php --help` for more informations


## Requirements

- Magento CE >= 1.6.x to 1.9.x and EE 1.14
- Cron enabled and configured for Magento (set your cron at server level to a period of 5 min to launch internal task related to the extension
*/5 * * * * php path/to/my/magento/cron.php)

## Documentation

- Login to Magento Backend and follow the menu System > Configuration > Diglin > Crawler
- Enable the configuration

## License

This extension is licensed under OSL v.3.0

## Support

Submit tickets on github

## Installation

### Via MagentoConnect

NaN

### Manually

```
git clone https://github.com/diglin/Diglin_Crawler.git
```

Then copy the files and folders in the corresponding Magento folders
Do not forget the folder "lib"

### Via modman

- Install [modman](https://github.com/colinmollenhour/modman)
- Use the command from your Magento installation folder: `modman clone https://github.com/diglin/Diglin_Crawler.git`

#### Via Composer

- Install [composer](http://getcomposer.org/download/)
- Create a composer.json into your project like the following sample:

```
 {
    "require" : {
        "diglin/diglin_crawler": "1.*"
    },
    "repositories" : [
        {
            "type": "vcs",
            "url": "git@github.com:diglin/Diglin_Crawler.git"
        }
    ]
 }
 ```
- Then from your composer.json folder: `php composer.phar install` or `composer install`

## Uninstall


### Via Magento Connect 

NaN

### Modman

modman can only remove files. So you can run the command `modman remove Diglin_Crawler` from your Magento root project however you will have to run the database cleanup procedure explained in the chapter "Manually" below.

### Manually

Remove the files or folders located into your Magento installation:
```
app/etc/modules/Diglin_Crawler.xml
app/code/community/Diglin/Crawler
shell/crawler.php
```

## Author

* Diglin GmbH
* http://www.diglin.com/
* [@diglin_](https://twitter.com/diglin_)
* [Follow me on github!](https://github.com/diglin)
