<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

use Topliner\Scheme\Placement;

$isObtain = require_once 'local.php';
$isSuccess = false;
$file = null;
if ($isObtain) {
    $placement = new Placement($DBType, $DBHost, $DBName, $DBLogin,
        $DBPassword);
    $points = $placement->getPoints();
    $json = json_encode($points);
    $json = "var points = $json;";
    $file = fopen(realpath(__DIR__) . '/points.js', 'w');
    $isSuccess = $file !== false;
}
if ($isSuccess) {
    $isSuccess = fwrite($file, $json);
    fclose($file);
}

