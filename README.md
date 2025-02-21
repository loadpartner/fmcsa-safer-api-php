# FMCSA SAFER API
A package to help interface with the Federal 

# Concepts
There are two kinds of reports returned by this library.

One is a "full report", which includes all the possible queries from the API for a given carrier.

The other is a "partial report", which will include only some basic information.

`searchCarrierName` returns partial reports, you should call the `getFullReport` with the DOT number to obtain the full version.

You can check if a report is full or not by the `full-report` property on the returned objects.

# Usage

You will need to obtain a key from the FMCSA, [you can do that here](https://mobile.fmcsa.dot.gov/QCDevsite/)

```php

use LoadPartner\FmcsaSaferApi\FmcsaSafer;

$apiKey = "xxxxx";  //your fmcsa safer API key
$svc = new FmcsaSafer($apiKey);
$svc->searchCarrierName('Trucks');  //returns a list of partial reports for matching carriers

// or search by exact DOT

$svc->getFullReport('1');   // returns the full report for a carrier based on DOT