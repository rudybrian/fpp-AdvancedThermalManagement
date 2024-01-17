<?php

include_once "/opt/fpp/www/common.php";

$pluginName = basename(dirname(__FILE__));
global $settingInfos;

if (isset($pluginSettings['DEBUG'])) {
    $DEBUG = $pluginSettings['DEBUG'];
}

$pluginConfigFile = $settings['configDirectory'] . "/plugin." . $pluginName;
if (file_exists($pluginConfigFile)) {
    $pluginSettings = parse_ini_file($pluginConfigFile);
}

function getEndpointsfppAdvancedThermalManagement() {
    $result = [
           [
           'method' => 'GET',
           'endpoint' => 'version',
           'callback' => 'fppAdvancedThermalManagementVersion'
           ],
           [
           'method' => 'GET',
           'endpoint' => 'fibsh',
           'callback' => 'fppAdvancedThermalManagementFibsh'
           ],
           [
           'method' => 'POST',
           'endpoint' => 'posty',
           'callback' => 'fppAdvancedThermalManagementPosty'
           ],
           [
           'method' => 'POST',
           'endpoint' => 'command',
           'callback' => 'fppAdvancedThermalManagementCommand'
           ]
        ];

    return $result;
}

// GET /api/plugin/fpp-AdvancedThermalManagement/version
function fppAdvancedThermalManagementVersion() {
    $result = array();
    $result['version'] = 'fpp-AdvancedThermalManagement v1.2.3';

    return json($result);
}

// GET /api/plugin/fpp-AdvancedThermalManagement/fibsh
function fppAdvancedThermalManagementFibsh() {
    $result = array();
    $result['fibsh'] = 'fpp-AdvancedThermalManagement fibsh v1.2.3';

    return json($result);
}

// POST /api/plugin/fpp-AdvancedThermalManagement/posty
function fppAdvancedThermalManagementPosty() {
    $result = array();
    if (isset($_POST['iam'])) {
         $result['iam'] = $_POST['iam'];
    }
    else {
        $result['iam'] = "The Walrus";
    }

    return json($result);
}

// POST /api/plugin/fpp-AdvancedThermalManagement/command
function fppAdvancedThermalManagementCommand() {
    $output = "";
    exec('python ' . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AdvancedThermalManagement_CLI.py --command jsondata \'' . json($_POST) .'\'  2>&1', $output, $return_val);
    #$result = array();
    if (implode($output) != "Error: Read failed") {
        return implode($output);
    }
    else {
        return "Nope!";
    }
}

?>
