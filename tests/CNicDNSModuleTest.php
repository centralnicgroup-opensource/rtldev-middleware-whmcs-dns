<?php

namespace CNicTest;

use PHPUnit\Framework\TestCase;

/**
 * CentralNic DNS Module Test
 *
 * PHPUnit test that asserts the fundamental requirements of a WHMCS
 * provisioning module.
 *
 * Custom module tests are added in addtion.
 *
 * @copyright Copyright (c) CentralNic Reseller 2022
 * @license https://github.com/centralnic-reseller/whmcs-dns/LICENSE
 */

class CNicDNSModuleTest extends TestCase
{
    /** @var string $moduleName */
    protected $moduleName = 'cnicdns';

    /**
     * Asserts the required config options function is defined.
     */
    public function testRequiredConfigOptionsFunctionExists()
    {
        $this->assertTrue(function_exists($this->moduleName . '_ConfigOptions'));
    }

    /**
     * Data provider of module function return data types.
     *
     * Used in verifying module functions return data of the correct type.
     *
     * @return array
     */
    public function providerFunctionReturnTypes()
    {
        return array(
            'Config' => array('config', 'array'),
            'Activate' => array('activate', 'array'),
            'Deactivate' => array('deactivate', 'array'),
            'Output' => array('output', 'null'),
        );
    }

    /**
     * Test module functions return appropriate data types.
     *
     * @param string $function
     * @param string $returnType
     *
     * @dataProvider providerFunctionReturnTypes
     */
    public function testFunctionsReturnAppropriateDataType($function, $returnType)
    {
        if (function_exists($this->moduleName . '_' . $function)) {
            $result = call_user_func($this->moduleName . '_' . $function, array());
            if ($returnType == 'array') {
                $this->assertTrue(is_array($result));
            } elseif ($returnType == 'null') {
                $this->assertTrue(is_null($result));
            } else {
                $this->assertTrue(is_string($result));
            }
        }
    }
}
