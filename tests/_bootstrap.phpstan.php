<?php

// This is the bootstrap for PHPStan.

$whmcsPath = realpath(__DIR__ . '/../../whmcs');
$configFile = __DIR__ . '/phpstan.config.php';
if (file_exists($configFile)) {
    include($configFile);
}

require_once $whmcsPath . '/vendor/autoload.php';
require_once $whmcsPath . '/includes/functions.php';

// stream_wrapper_restore('phar');
