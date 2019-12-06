<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

use Topliner\Scheme\Construct;

$isObtain = require_once 'local.php';
$isSuccess = false;
$file = null;
if ($isObtain) {
    $construct = new Construct();
    $points = $construct->get();
    $json = json_encode($points);
    $file = fopen(realpath(__DIR__) . '/js/points.json', 'w');
    $isSuccess = $file !== false;
}
if ($isSuccess) {
    $isSuccess = fwrite($file, $json);
    fclose($file);
}
echo ($isSuccess ? 'Success' : 'Fail') . ' write `/js/points.json`';
