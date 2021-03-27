<?php

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

?>
