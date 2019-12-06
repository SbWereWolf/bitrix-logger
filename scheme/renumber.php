<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

use Topliner\Scheme\Renumber;

$isLocalRun = require_once 'local.php';

if ($isLocalRun) {
    $result = (new Renumber())->run();

    var_export($result);
}