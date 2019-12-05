<?php

use Topliner\Scheme\Renumber;

$isLocalRun = require_once 'local.php';

if ($isLocalRun) {
    $result = (new Renumber())->run();

    var_export($result);
}