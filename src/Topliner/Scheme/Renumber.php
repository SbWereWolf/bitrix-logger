<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

namespace Topliner\Scheme;


use CDatabase;
use CIBlockElement;
use CModule;
use mysqli;
use mysqli_stmt;
use Topliner\Bitrix\BitrixOrm;
use Topliner\Bitrix\InfoBlock;

class Renumber
{
    /**
     * @param $id
     * @param mysqli_stmt $query
     * @param mysqli $dbConn
     * @return bool
     */
    public static function storeNumber($id, mysqli_stmt $query,
                                       mysqli $dbConn)
    {
        $isSuccess = $query->execute() !== false;
        if ($isSuccess) {
            $number = $dbConn->insert_id;

            CIBlockElement::SetPropertyValuesEx(
                $id, (BitrixScheme::getConstructs())->getBlock(),
                ['number' => $number], ['NewElement' => true]);
        }
        return $isSuccess;
    }

    /**
     * @param mysqli $dbConn
     * @return false|mysqli_stmt
     */
    public static function prepare(mysqli $dbConn)
    {
        $query = $dbConn->prepare('
insert into a_construct_number VALUE (DEFAULT)
');
        return $query;
    }

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
            $query = static::prepare($dbConn);
            $isSuccess = $query !== false;
        }
        foreach ($ids as $key => $id) {
            if ($isSuccess) {
                Logger::$operation = Logger::CHANGE;
            }
            $isSuccess = static::storeNumber($id, $query, $dbConn);
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