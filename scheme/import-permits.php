<?php

use Bitrix\Main\Config\Configuration;
use Topliner\Scheme\ImportPermits;

const OBTAIN = 'obtain';
$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__) . '/..';

require_once($_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php');
require_once($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_before.php");
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);

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
if ($isObtain) {
    $placement = new ImportPermits($DBType, $DBHost, $DBName, $DBLogin,
        $DBPassword);
    $placement->run();
}

