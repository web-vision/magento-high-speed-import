Magento-High-Speed-Import
=========================

By default, Magento only offers basic functionalities for importing products, e.g. from an ERP system, and making them available in the store. Especially with a large number of several thousand products, the standard Magento Dataflow interface is too slow. Product imports do not take between 8-20 seconds per product.

The automatic assignment of categories, image imports or even a logical import of configurable products (variant articles) with associated individual products has not been possible at all up to now with Magento on-board means.

With the Magento Import Extension developed by web-vision, you can now reliably and securely import thousands of products into your Magento store within a few minutes - for Magento 1 and Magento 2.

## Performance
Import 16,000 products into a running Magento store in live mode within 8 minutes.

## What can the MHSI do for you?
-  Graphical user interface for creating, managing import profiles and for manual execution.
-  XML to CSV conversion.
-  Import of configurable and simple products in Magento 1 or 2.
-  Import of up-selling, cross-selling and accessory products.
-  Import of special prices for customer groups (from Magento 1.7)
-  Import of graduated prices for customer groups (from Magento 1.7)
-  Import of CSV data (directly) or XML data (after previous conversion in MHSI)
-  Any number of configurable import profiles (e.g. for different data formats from different manufacturers)
-  Complete import of simple and configurable products (= variant articles with any attributes) and their assignment among each other
-  Automatic import of attribute values of any kind incl. filter or selection options
-  Import of any number of images
-  Automatic assignment of images based on the article number
-  Assignment of products to the respective categories
-  Categories can be assigned optionally by IDs and category names
-  Categories can be nested product-related
-  Automatic creation of non-existing categories in Magento 
-  Output of error messages in case of faulty import file
-  Time-controlled import via CronJob possible (e.g. for stock updates)
-  Update mode - e.g. for updating individual attributes such as names, stock quantities or sizes

## Getting started
### Installation
Unzip the ZIP file on your computer, and then copy the mhsi folder and the license-mhsi.txt file to the root directory of your Magento store using an FTP program, or clone it directly to the Magento installation folder. The folder has to be placed in the same directory, such as the app/ or skin/ folders.

### Base configuration
The Magento High Speed Import interface is delivered with a basic configuration, which assumes the following prerequisites:

-  The import file is called import_all_products.csv and is located in the var/import directory below the Magento root directory.
-  The CSV file uses the semicolon ( ; ) as field separator and the double quotes above as text delimiter for each field. ( " )
-  The default attribute set is the Magento default attribute set.
-  Website and Store are the Magento default values
-  All products use ID 2 (usually 19% VAT) of Magento
-  All products are active and visible after import.
-  The category names are in a column categories. If a category does not yet exist in Magento, it will be created. Commas are generally used as category separators, categories can be nested with the hash sign ( # ). Additionally, a colon at the end of the category can be used to determine the position of the product within the category.

Example: Main category#Subcategory1:100,Main category#Subcategory2:20

The mapping of product attributes and setting of Magento default values can be done with Configuration.xml files which are located in bin/ folder. Please find a first example here: bin/default.xml.dist. Import files shall be placed in the var/import folder. 

### Run your first import 
Just call the MHSI with your URL: https://youronlinehsopurl.com/mhsi
Please notice, that we **strongly recommend to protect this folder by htpasswd / htaccess protection**. 

### Documentation
At the moment a more enhnaced German version of the package can be found here: 
https://support.web-vision.de/produkte-und-software/magento-high-speed-import-3-1

## Warranty
This script comes without any warranty. Please use it at your own risk and make sure to take backups and test the script in a staging / development environment before you run it on a production system.

## Donation / License
This script ist "donate ware" under GPL3.0. We would be glad, if the script is useful for you, if you could donate something via paypal. If you need an invoice, please leave a comment in your donation.

## PayPal Donation Button

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HDGBRLCFRTVPA)
