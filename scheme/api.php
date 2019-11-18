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
    $stream = fopen('php://input', 'r');
    $data = stream_get_contents($stream);
    fclose($stream);
    $parameters = json_decode($data, true);
    $output = (new Api($parameters))->run();
}
$result = json_encode($output);

echo $result;