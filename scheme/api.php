<?php


$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__) . '/..';

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_before.php');

use LanguageSpecific\ArrayHandler;

/* @var $APPLICATION CMain */
global $APPLICATION;
/* @var $DB CDatabase */
global $DB;
/* @var $USER CUser */
global $USER;
$cookies = new ArrayHandler($_COOKIE);
$login = $cookies->get('api-login')->str();
$hash = $cookies->get('api-hash')->str();
$isSuccess = $USER->LoginByHash($login, $hash);
if ($isSuccess) {
    $data = stream_get_contents(fopen('php://input', 'r'));
    $parameters = json_decode($data, true);

}