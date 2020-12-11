# Generate PDF documents with tables displaying data

 ![Latest Stable Version](https://img.shields.io/badge/release-v1.2.0-brightgreen.svg)
 ![License](https://img.shields.io/packagist/l/gomoob/php-pushwoosh.svg) 
 [![Donate](https://img.shields.io/static/v1?label=donate&message=PayPal&color=orange)](https://www.paypal.me/SKientzler/5.00EUR)
 ![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)
 [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Stefanius67/XFPDF/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/Stefanius67/XFPDF/?branch=main)
 
----------
## Dependency
this package contains a modified version of FPDF (based on Version 1.82 from 2019-12-07)
with following changes:
- PHP 7.4 typehinting
- integrated the bookmark extension from http://www.fpdf.org/
- phpDoc comments (content from the FPDF-Manual at http://www.fpdf.org/)
- PSR coding style (camel case methods, spacing, indentation, brackets)
- namespace for PSR-4 autoloading
- some fixes according to phpStan/scrutinizer inspections

## Overview

This class can generate PDF reports with tables displaying data.

It is an extension of the FPDF that takes arrays with data to display on the rows of a 
table and then it can generate a PDF document that displays the table on a page.

The class allows to configure the orientation of the table on the page. 
Other attributes can be set using functions of the FPDF base class.


In addition to extensive column definitions such as
- Date values
- Currency information
- formatted numerical values
- graphic symbols

the package offers the possibility to automatically insert totals, subtotals and page transfers.

## History
##### 2020-03-06	Version 1.00
  * initial Version
  
##### 2020-03-24	Version 1.0.1
  * added functions for total, subtotals and carryover
  * added formating for currency, number and datetime
  * fixed some problems with euro-sign and german umlauts
					
##### 2020-04-21	Version 1.0.2
  * Integrated extension to set bookmarks from O.Plathey
  * Added new functions to set internal links within the datagrid
  
##### 2020-11-23	Version 1.1.0
  * Moved XPDFFont into a separate file to correspond to PSR-0 / PSR-4 (one file per class) 
  * Set namespace to fit PSR-4 recommendations for autoloading.
  * Added missing PHP 7.4 type hints / docBlock changes 
  * Support of image columns 
  * Added separate font for subject in page header (2'nd line) 
  * customizeable Height of the header logo  

