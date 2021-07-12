# WHMCS "CentralNic" DNS Module #

[![semantic-release](https://img.shields.io/badge/%20%20%F0%9F%93%A6%F0%9F%9A%80-semantic--release-e10079.svg)](https://github.com/semantic-release/semantic-release)
[![Build Status](https://github.com/centralnic-reseller/whmcs-dns/workflows/Release/badge.svg?branch=master)](https://github.com/centralnic-reseller/whmcs-dns/workflows/Release/badge.svg?branch=master)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PRs welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](https://github.com/centralnic-reseller/whmcs-dns/blob/master/CONTRIBUTING.md)

This Repository covers the WHMCS DNS Module of CentralNic. It provides the following features in WHMCS:

* DNS Templating
* Management of DNS Zones and threir RRs

## Requirements ##

* PHP 7.3 or 7.4
* WHMCS 8.0 or greater
* [HEXONET/ispapi](https://github.com/hexonet/whmcs-ispapi-registrar#readme) or [RRPproxy/keysystems](https://github.com/rrpproxy/whmcs-rrpproxy-registrar#readme) Registrar Module

## Installation ##

1. Upload the `modules/cnicdns/` folder to the `modules/addons/` folder of your WHMCS installation
2. Open the Admin Area and navigate to `System Settings` => `Addon Modules`
3. Find the `CentralNic DNS` module and click on `Activate`
4. Click on `Configure` and make sure to set proper access controls
5. Click on `Save Changes` to save module configuration

## Configuration ##

1. Open the Admin Area and navigate to `Addons` => `CentralNic DNS`
2. Click on `Add` to create your first template
3. Fill out the form
   * `Name` can be anything you want
   * `Zone` contains the actual zone template
   * `Products/Services` determines if the template should be the default for any of the selected products. *Note that selecting a product for a template will unselect it for any other product, as there can be only one template per product.*
   * `Set as global default` determines if the template should apply if no product association matches. *Note that setting as global default will unset any other template, as there can be onle one global default template.*
4. Click `Save` to create the new template

## Zone formatting ##

Write one record per line in this format:
`<hostname> <type> <address> (<priority>)`

* `type` should one of the following: A, AAAA, MX, MXE, CNAME, TXT, SRV, URL, FRAME
* You can use the `@` shortcut for using the domain name in the `hostname` or `address` part
* You can use the `%ip%` shortcut for assigning the IP based on the server in the `address` part
* `priority` is only necessary for MX records

Example:

    @ A %ip%
    www CNAME @
    mail A 127.0.0.1
    @ MX mail.@ 10

## Zone application ##

The DNS template will be automatically applied under the following conditions:

* The domain is assigned to a supported registrar module (currently `ispapi` and `keysystems` are supported)
* The domain has the `DNS Management` feature activated
* There is a hosting package in WHMCS for that domain, and a zone template that is assigned as default for that package, *or*
* There is a zone template configured as global default

## Troubleshooting ##

If the module does not work as intended, e.g. a template is not being applied as expected, please follow the following steps:

1. Make sure the [`conditions`](#zone-application) are met
2. If they are, open the Admin Area and navigate to `Addons` => `CentralNic DNS`
3. Click on the `Logs` button to check the log entries for the addon
4. If there is any error message, please check that the zone template is [`formatted correctly`](#zone-formatting)

## Resources ##

* [Documentation](https://centralnic-reseller.github.io/centralnic-reseller/docs/centralnic/whmcs/whmcs-dns/)
* [Release Notes](https://github.com/centralnic-reseller/whmcs-dns/releases)

## Authors ##

* **Kai Schwarz** - *lead development* - [PapaKai](https://github.com/papakai)
* **Sebastian Vassiliou** - *development* - [h9k](https://github.com/h9k)

## License ##

This project is licensed under the MIT License - see the [LICENSE](https://github.com/centralnic-reseller/whmcs-dns/blob/master/LICENSE) file for details.

[CentralNic Group PLC](https://centralnicgroup.com)
