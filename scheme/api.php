<?php


$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__) . '/..';

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_before.php');

use LanguageSpecific\ArrayHandler;
use Topliner\Scheme\Api;

/* @var $APPLICATION CMain */
global $APPLICATION;
/* @var $DB CDatabase */
global $DB;
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
    $output = (new Api($parameters))->run();
}
$result = json_encode($output);

echo $result;