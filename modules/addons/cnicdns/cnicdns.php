<?php

/**
 * CentralNic DNS Addon for WHMCS
 *
 * DNS management using WHMCS & CentralNick brands
 *
 * For more information, please refer to the online documentation.
 * @see https://centralnic-reseller.github.io/centralnic-reseller/
 * @noinspection PhpUnused
 */

require_once(__DIR__ . '/vendor/autoload.php');

use CNIC\WHMCS\DNS\DNSHelper;
use CNIC\WHMCS\DNS\Product;
use CNIC\WHMCS\DNS\Template;

/**
 * Configuration of the addon module.
 * @return array<string, mixed>
 */
function cnicdns_config(): array
{
    return [
        "name" => "CentralNic DNS",
        "description" => "Configure DNS templates for your domains",
        "author" => '<a href="https://www.centralnicgroup.com/" target="_blank"><img style="max-width:100px" src="' . DNSHelper::getLogo() . '" alt="CentralNic" /></a>',
        "language" => "english",
        "version" => "0.0.0"
    ];
}

/**
 * This function will be called with the activation of the add-on module.
 * @return array<string, string>
 */
function cnicdns_activate(): array
{
    DNSHelper::createSchema();
    return ['status' => 'success','description' => 'Installed'];
}

/**
 * @param array<string, mixed> $vars
 */
function cnicdns_upgrade(array $vars): void
{
    DNSHelper::updateSchema($vars['version']);
}

/**
 * This function will be called with the deactivation of the add-on module.
 * @return array<string, string>
 */
function cnicdns_deactivate(): array
{
    DNSHelper::dropSchema();
    return ['status' => 'success','description' => 'Uninstalled'];
}

/**
 * Module interface functionality
 * @param array<string, mixed> $vars
 */
function cnicdns_output(array $vars): void
{
    global $templates_compiledir;

    if (!DNSHelper::compatibleRegistrarActive()) {
        echo "<div class=\"alert alert-danger\">{$vars['_lang']['errorRegistrar']}</div>";
        return;
    }

    switch (@$_GET['page']) {
        case 'service':
            cnicdns_output_service($_REQUEST['action'], @$_REQUEST['id'], $vars);
            break;
        default:
            $smarty = new Smarty();
            $smarty->setTemplateDir(__DIR__ . DIRECTORY_SEPARATOR . 'templates');
            $smarty->setCompileDir($templates_compiledir);
            $smarty->setCaching(Smarty::CACHING_OFF);
            $smarty->assign('lang', $vars['_lang']);
            $smarty->assign('modulelink', @$vars["modulelink"]);

            try {
                $smarty->assign('logo', DNSHelper::getLogo());
                $smarty->assign('products', Product::getAll());
            } catch (Exception $ex) {
                $smarty->assign('error', $ex->getMessage());
            }

            try {
                $smarty->display("templates.tpl");
            } catch (Exception $e) {
                echo "<div class=\"alert alert-danger\">{$vars['_lang']['error']} - {$vars['_lang']['errorRender']} : {$e->getMessage()}</div>";
            }
    }
}

/**
 * Handle the XHR requests
 * @param string $actionName
 * @param int|null $actionId
 * @param array<string, mixed> $vars
 */
function cnicdns_output_service(string $actionName, ?int $actionId, array $vars): void
{
    header('Content-Type: application/json');
    $response = [];
    try {
        switch ($actionName) {
            case 'getTemplates':
                $response = ['data' => Template::getAll()];
                break;
            case 'getTemplate':
                if ($actionId == null) {
                    throw new Exception('Missing parameter');
                }
                $response = Template::get($actionId);
                break;
            case 'createTemplate':
                $response = Template::create();
                break;
            case 'editTemplate':
                if ($actionId == null) {
                    throw new Exception('Missing parameter');
                }
                $response = Template::edit($actionId);
                break;
            case 'deleteTemplate':
                if ($actionId == null) {
                    throw new Exception('Missing parameter');
                }
                $response = Template::delete($actionId);
                break;
            default:
                http_response_code(404);
                $response['error'] = 'Unknown action';
        }
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
