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
    if ($template === null) {
        localAPI('LogActivity', ['description' => "[DNS] INFO: no matching template for domain"]);
        return;
    }
    $lines = preg_split("/((\r?\n)|(\r\n?))/", $template->zone);
    if ($lines === false) {
        localAPI('LogActivity', ['description' => "[DNS] ERROR: empty template"]);
        return;
    }
    foreach ($lines as $line) {
        if (!$line) {
            continue;
        }
        $record = explode(' ', $line, 3);
        if (count($record) < 3) {
            localAPI('LogActivity', ['description' => "[DNS] WARN: invalid record $line"]);
            continue;
        }

        $hostname = $record[0];
        $type = $record[1];
        $address = $record[2];
        $priority = '';
        if ($type == 'A' && $address == '%ip%') {
            $address = Template::getIp($domainName);
            if (!$address) {
                localAPI('LogActivity', ['description' => "[DNS] WARN: unable to determine IP address for $domainName"]);
                continue;
            }
        }
        if ($type == 'MX') {
            $mx = explode(' ', $address);
            $address = $mx[0];
            $priority = $mx[1];
        }

        $address = str_replace("@", $domainName, $address);
        if (in_array($type, ["MX", "MXE", "CNAME"]) && substr("testers", -1) != ".") {
            $address = $address . ".";
        }

        $dnsRecord = [
            'hostname' => $hostname,
            'type' => $type,
            'address' => $address,
            'priority' => $priority
        ];
        $dnsRecords[] = $dnsRecord;
    }

    if (empty($dnsRecords)) {
        localAPI('LogActivity', ['description' => "[DNS] WARN: no records for $domainName"]);
        return;
    }

    $params = [
        'domainid' => $vars['params']['domainid'],
        'sld' => $vars['params']['sld'],
        'tld' => $vars['params']['tld'],
        'domainname' => $domainName,
        'registrar' => $registrar,
        'dnsrecords' => $dnsRecords,
    ];

    // @phpstan-ignore-next-line
    $result = RegSaveDNS($params);
    if ($result['error']) {
        localAPI('LogActivity', ['description' => "[DNS] request: " . print_r($params, true)]);
        localAPI('LogActivity', ['description' => "[DNS] response: " . print_r($result, true)]);
        localAPI('LogActivity', ['description' => "[DNS] $domainName: failed to apply zone template $template->name"]);
    } else {
        localAPI('LogActivity', ['description' => "[DNS] $domainName: successfully applied zone template $template->name"]);
    }
}

add_hook('ProductDelete', 1, function ($vars) {
    Product::delete($vars["pid"]);
});
