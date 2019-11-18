<?
header("Location: /index.html");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Демонстрационная версия продукта «1С-Битрикс: Управление сайтом»");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Главная страница");
?><h2>Hello world, it is a Scheme!</h2><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>