<?
/*
You can place here your functions and event handlers

AddEventHandler('module', 'EventName', 'FunctionName');
function FunctionName(params)
{
	//code
}
*/

use Topliner\Scheme\Logger;
use Topliner\Scheme\PermitTab;

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

if (!defined('IBLOCK_MODULE')) {
    define('IBLOCK_MODULE', 'iblock');
}

AddEventHandler(IBLOCK_MODULE, 'OnIBlockElementAdd',
    Array(Logger::class, 'OnAdd'));
AddEventHandler(IBLOCK_MODULE, 'OnAfterIBlockElementAdd',
    Array(Logger::class, 'afterAdd'));

AddEventHandler(IBLOCK_MODULE, 'OnBeforeIBlockElementUpdate',
    Array(Logger::class, 'beforeUpdate'));
AddEventHandler(IBLOCK_MODULE, 'OnIBlockElementUpdate',
    Array(Logger::class, 'OnUpdate'));
/*AddEventHandler(IBLOCK_MODULE, 'OnStartIBlockElementUpdate',
    Array(Logger::class, 'startUpdate'));*/
AddEventHandler(IBLOCK_MODULE, 'OnAfterIBlockElementUpdate',
    Array(Logger::class, 'afterUpdate'));

/*AddEventHandler(IBLOCK_MODULE, 'OnIBlockElementDelete',
    Array(Logger::class, 'OnDelete'));*/
AddEventHandler(IBLOCK_MODULE, 'OnBeforeIBlockElementDelete',
    Array(Logger::class, 'beforeDelete'));
AddEventHandler(IBLOCK_MODULE, 'OnAfterIBlockElementDelete',
    Array(Logger::class, 'afterDelete'));

AddEventHandler(IBLOCK_MODULE, 'OnIBlockElementSetPropertyValues',
    Array(Logger::class, 'OnSetPropertyValues'));
AddEventHandler(IBLOCK_MODULE, 'OnAfterIBlockElementSetPropertyValues',
    Array(Logger::class, 'afterSetPropertyValues'));

AddEventHandler(IBLOCK_MODULE, 'OnIBlockElementSetPropertyValuesEx',
    Array(Logger::class, 'OnSetPropertyValuesEx'));
AddEventHandler(IBLOCK_MODULE, 'OnAfterIBlockElementSetPropertyValuesEx',
    Array(Logger::class, 'afterSetPropertyValuesEx'));

AddEventHandler('main', 'OnAdminIBlockElementEdit',
    Array(PermitTab::class, 'OnInit'));
