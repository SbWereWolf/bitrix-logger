<?php

use Bitrix\Main\Config\Configuration;
use Topliner\Scheme\Placement;

const OBTAIN = 'obtain';
$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__) . '/..';

require_once($_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/header.php');

/* @var $APPLICATION CMain */
global $APPLICATION;
/* @var $DB CDatabase */
global $DB;

$isExists = false;
$isObtain = getenv(OBTAIN, true);
if (!$isObtain) {
    $isExists = key_exists(OBTAIN, $_GET);
}
if (!$isObtain && $isExists) {
    $isObtain = $isObtain ||
        Configuration::getValue(OBTAIN) === $_GET[OBTAIN];
}
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
    fwrite($file, $json);
    fclose($file);
}

