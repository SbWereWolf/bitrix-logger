<?php

use Topliner\Scheme\Construct;

const OBTAIN = 'obtain';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__) . '/..';

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_before.php');
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);

/* @var $APPLICATION CMain */
global $APPLICATION;
/* @var $DB CDatabase */
global $DB;

$isExists = false;
$isObtain = getenv(OBTAIN, true);
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
