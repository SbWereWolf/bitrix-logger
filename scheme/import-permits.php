<?php

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
$isSuccess = false;
if ($isObtain) {
    /**
     * @global CUser $USER
     */
    global $USER;
    $isSuccess = $USER->Authorize(1);

    if ($isSuccess) {
        $import = new ImportPermits($DBType, $DBHost, $DBName, $DBLogin,
            $DBPassword);
        $import->run();
    }
}

