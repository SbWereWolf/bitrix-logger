<?
//http://scheme.topliner.ru/?back_url_admin=%2Fbitrix%2Fadmin%2Fsite_edit.php%3Flang%3Dru%26LID%3D22
if(!$_GET['back_url_admin'])header("Location: /index.html");
else header("Location: /scheme/edit.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Демонстрационная версия продукта «1С-Битрикс: Управление сайтом»");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Главная страница");
?><h2>Hello world, it is a Scheme!</h2><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>