<?php

use Topliner\Scheme\ImportConstruction;

const OBTAIN = 'obtain';
$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__) . '/..';

require_once($_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php');
require_once($_SERVER["DOCUMENT_ROOT"]
    . "/bitrix/modules/main/include/prolog_before.php");
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);

$isObtain = getenv(OBTAIN, true);
$isSuccess = false;
if ($isObtain) {
    $placement = new ImportConstruction($DBType, $DBHost, $DBName, $DBLogin,
        $DBPassword);
    $placement->run();
}

