<?php

/**
 * CentralNic DNS Addon for WHMCS
 *
 * DNS management using WHMCS & CentralNic brands
 *
 * For more information, please refer to the online documentation.
 * @see https://centralnic-reseller.github.io/centralnic-reseller/
 * @noinspection PhpUnused
 */

use CNIC\WHMCS\DNS\DNSHelper;
use CNIC\WHMCS\DNS\Product;
use CNIC\WHMCS\DNS\Template;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once(__DIR__ . '/vendor/autoload.php');

add_hook("AfterRegistrarRegistration", 1, 'cnicdns_apply');

/**
 * @param array<string, mixed> $vars
 */
function cnicdns_apply(array $vars): void
{
    if (isset($vars['functionSuccessful']) && $vars['functionSuccessful'] === false) {
        return;
    }
    if ($vars['dnsmanagement'] === false) {
        return;
    }
    $registrar = DNSHelper::getDomainRegistrar($vars['params']['domainid']);
    if (!in_array($registrar, ['ispapi', 'keysystems'])) {
        return;
    }

    $domainName = "{$vars['params']['sld']}.{$vars['params']['tld']}";
    $template = Template::getForDomain($domainName);

    $dnsRecords = [];
    if (!$template) {
        localAPI('LogActivity', ['description' => "[DNS] ERROR: empty template"]);
        return;
    }
    $lines = preg_split("/((\r?\n)|(\r\n?))/", $template);
    if ($lines === false) {
        localAPI('LogActivity', ['description' => "[DNS] ERROR: empty template"]);
        return;
    }
    foreach ($lines as $line) {
        $record = explode(' ', $line, 3);
        if (count($record) < 3) {
            continue;
        }

        $hostname = $record[0];
        $type = $record[1];
        $address = $record[2];
        $priority = '';
        if ($type == 'A' && $address == '%ip%') {
            $address = Template::getIp($domainName);
            if (!$address) {
                continue;
            }
        }
        if ($type == 'MX') {
            $mx = explode(' ', $address);
            $address = $mx[0];
            $priority = $mx[1];
        }
        $dnsRecord = [
            'hostname' => $hostname,
            'type' => $type,
            'address' => $address == '@' ? $domainName : $address,
            'priority' => $priority
        ];
        $dnsRecords[] = $dnsRecord;
    }

    $params = [
        'domainid' => $vars['params']['domainid'],
        'sld' => $vars['params']['sld'],
        'tld' => $vars['params']['tld'],
        'domainname' => $domainName,
        'registrar' => $registrar,
        'dnsrecords' => $dnsRecords,
    ];

//    localAPI('LogActivity', ['description' => print_r($params, true)]);

    // @phpstan-ignore-next-line
    $result = RegSaveDNS($params);
    if ($result['error']) {
        localAPI('LogActivity', ['description' => "{$domainName}: failed to apply zone template [DNS]"]);
    } else {
        localAPI('LogActivity', ['description' => "{$domainName}: successfully applied zone template [DNS]"]);
    }
}

add_hook('ProductDelete', 1, function ($vars) {
    Product::delete($vars["pid"]);
});
