<?php

use Topliner\Scheme\ImportPermits;

$isObtain = require_once 'local.php';
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

