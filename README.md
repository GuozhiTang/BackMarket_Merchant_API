# Backmarket Merchant API
![](https://img.shields.io/badge/php-^7.3.11-blue.svg)<br>

A PHP script bundle built for the merchant of [Back Market](https://www.backmarket.com/) which includes basic API functions and basic functions for dealing with listings and orders.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

* Your own PHP environment should be settled down, you can check the PHP version by running:

```Bash
php --version
```

## Configuration

### Database

* The database of this repo is MySQL. So you should configure your own database before running the script.

## Running the project

* Run the command to start the script for dealing with listings and orders:

```Bash
/usr/bin/wget http://localhost/.../BackMarket_Merchant_API/testscripts/bm_listings.php
/usr/bin/wget http://localhost/.../BackMarket_Merchant_API/testscripts/bm_orders.php
```

* (Optional) You can choose to run the script in the directory `./testscripts/logclean`:

  * Run the `folderclean.php` script for cleaning logs periodically:

  ```Bash
  /usr/bin/wget http://localhost/.../BackMarket_Merchant_API/testscripts/logclean/bm_orders.php
  ```

## Built With

* [PHP](https://www.php.net/) - The web script language.
* [MySQL](https://www.mysql.com/) - The database for the scripts in this repo.