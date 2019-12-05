<?php


namespace Topliner\Scheme;


use CDatabase;
use CIBlockElement;
use CModule;
use mysqli;
use Topliner\Bitrix\BitrixOrm;
use Topliner\Bitrix\InfoBlock;

class Renumber
{
    public function run()
    {
        CModule::IncludeModule(InfoBlock::MODULE);

        /* @var $DB CDatabase */
        global $DB;

        /** @var $dbConn mysqli */
        $dbConn = $DB->db_Conn;
        $dbConn->begin_transaction();
        $dbConn->autocommit(false);
        $dbConn->query('SET unique_checks=0');
        $dbConn->query('SET foreign_key_checks=0');

        $constructs = BitrixScheme::getConstructs();
        $filter = [InfoBlock::IBLOCK_ID => $constructs->getBlock(),
            InfoBlock::SECTION_ID => $constructs->getSection(),
            'PROPERTY_number' => false];
        $ids = BitrixOrm::getIdOfAll($filter);

        $query = null;
        $isSuccess = !empty($ids);
        if ($isSuccess) {
            $query = $dbConn->prepare('
insert into a_construct_number VALUE (DEFAULT)
');
            $isSuccess = $query !== false;
        }
        foreach ($ids as $key => $id) {

            if ($isSuccess) {
                $isSuccess = $query->execute() !== false;
            }
            if ($isSuccess) {
                $number = $dbConn->insert_id;

                Logger::$operation = Logger::CHANGE;
                CIBlockElement::SetPropertyValuesEx(
                    $id, $constructs->getBlock(),
                    ['number' => $number]);
            }
            if (!$isSuccess) {
                break;
            }

        }

        $dbConn->query('SET unique_checks=1');
        $dbConn->query('SET foreign_key_checks=1');
        $dbConn->autocommit(true);

        if ($isSuccess) {
            $dbConn->commit();
        }
        if (!$isSuccess) {
            $dbConn->rollback();
        }

        $output = ['success' => $isSuccess];
        return $output;
    }

}