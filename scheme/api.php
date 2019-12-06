<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__) . '/..';

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_before.php');

use LanguageSpecific\ArrayHandler;
use Topliner\Scheme\Api;

/* @var $USER CUser */
global $USER;
$cookies = new ArrayHandler($_COOKIE);
$login = $cookies->get('api-login')->str();
$hash = $cookies->get('api-hash')->str();

$output = ['success' => false, 'message' => 'Not authorized'];
$isSuccess = $USER->LoginByHash($login, $hash);
if ($isSuccess) {
    $data = $_POST['data'];
    $parameters = json_decode($data, true);
    $parameters['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
    set_time_limit(600);
    $output = (new Api($parameters))->run();
}
$result = json_encode($output);

echo $result;