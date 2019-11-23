<?php

namespace Topliner\Scheme;

use CDatabase;
use CIBlockElement;
use CModule;
use Exception;
use mysqli;
use PDO;

class ImportPermits
{
    const SHORT = 'SHORT';
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $base;
    /**
     * @var string
     */
    private $login;
    /**
     * @var string
     */
    private $password;
    /**
     * @var BitrixSection
     */
    private $permit = null;

    /**
     * ImportPermits constructor.
     * @param string $type
     * @param string $host
     * @param string $base
     * @param string $login
     * @param string $password
     */
    public function __construct($type, $host, $base, $login, $password)
    {
        $this->type = $type;
        $this->host = $host;
        $this->base = $base;
        $this->login = $login;
        $this->password = $password;
    }

    public function run()
    {
        $connection = null;
        try {
            $connection = new PDO(
                "{$this->type}:host={$this->host};dbname={$this->base}",
                $this->login, $this->password);
            $isSuccess = true;
        } catch (Exception $e) {
            $isSuccess = false;
        }
        if ($connection === null) {
            echo 'не могу соединиться с базой' . PHP_EOL;
        }
        $getPermits = null;
        if ($isSuccess) {
            $getPermits = $connection->prepare("
SELECT
       rp.permit,rp.remark,
       tp.issuing_at,tp.start,tp.finish, 
       da.UF_XML_ID distributor
FROM
     raw_permit rp
     join tx_permit tp
     on rp.permit = tp.permit
     and rp.unixtime = tp.issuing_at
     join b_hlbd_distributors_of_ads da
     on da.UF_NAME = rp.distributor
ORDER BY tp.issuing_at,tp.permit
");
            $isSuccess = $getPermits !== false;
        }
        if ($isSuccess) {
            $command = $connection->exec('SET NAMES \'utf8mb4\''
                . ' COLLATE \'utf8mb4_unicode_ci\'');
            $isSuccess = $command !== false;
        }
        if ($isSuccess) {
            $command = $connection->exec('START TRANSACTION');
            $isSuccess = $command !== false;
        }
        if ($isSuccess) {
            $isSuccess = $getPermits->execute();
        }
        $bulkPermits = [];
        if ($isSuccess) {
            $bulkPermits = $getPermits->fetchAll(PDO::FETCH_ASSOC);
        }
        if (!empty($connection)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $command = $connection->exec('ROLLBACK');
            $connection = null;
        }

        CModule::IncludeModule("iblock");
        /* @var $DB CDatabase */
        global $DB;

        /** @var $dbConn mysqli */
        $dbConn = $DB->db_Conn;
        $dbConn->begin_transaction();
        $dbConn->autocommit(false);
        $dbConn->query('SET unique_checks=0');
        $dbConn->query('SET foreign_key_checks=0');

        $this->permit = BitrixScheme::getPermits();
        foreach ($bulkPermits as $item) {

            $date = ConvertTimeStamp(time(), 'FULL');
            $issuingAt = ConvertTimeStamp($item['issuing_at'], self::SHORT);
            $fields = array(
                'IBLOCK_ID' => $this->permit->getBlock(),
                'IBLOCK_SECTION_ID' => $this->permit->getSection(),
                'ACTIVE_FROM' => $date,
                'NAME' => "Разрешение №{$item['permit']} от $issuingAt",
            );

            $element = new CIBlockElement();
            $id = $element->Add($fields);

            $isSuccess = !empty($id);
            if (!$isSuccess) {
                $details = var_export($item, true);
                echo("Fail add element : $details" . PHP_EOL);
            }
            if ($isSuccess) {
                $start = ConvertTimeStamp($item['start'], self::SHORT);
                $finish = ConvertTimeStamp($item['finish'], self::SHORT);
                CIBlockElement::SetPropertyValuesEx($id,
                    $this->permit->getBlock(),
                    array(
                        'number' => $item['permit'],
                        'issuing_at' => $issuingAt,
                        'start' => $start,
                        'finish' => $finish,
                        'distributor' => $item['distributor'],
                        'remark' => $item['remark'],
                    ));
            }
        }

        $dbConn->query('SET unique_checks=1');
        $dbConn->query('SET foreign_key_checks=1');
        $dbConn->autocommit(true);
        $dbConn->commit();
    }
}