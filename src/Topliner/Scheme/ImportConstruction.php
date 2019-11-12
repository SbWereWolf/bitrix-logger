<?php

namespace Topliner\Scheme;

use CDatabase;
use CIBlockElement;
use CModule;
use Exception;
use mysqli;
use PDO;

class ImportConstruction
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
    private $constructions = null;
    /**
     * @var BitrixSection
     */
    private $permit = null;

    public function __construct(string $type, string $host, string $base,
                                string $login, string $password)
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
        $getConstruction = null;
        if ($isSuccess) {
            $getConstruction = $connection->prepare("
select
    rp.remark, sc.x,sc.y,
    ct.UF_XML_ID construction, ct.UF_NAME,
    tp.issuing_at,tp.permit
from
    tx_scheme_construction sc
    join tx_type tt
    on sc.type = tt.id
    join b_hlbd_construction_types ct
    on ct.UF_XML_ID = tt.code
    left join tx_permit_scheme_construction pc
    on sc.uid = pc.tx_scheme_construction_uid
    left join tx_permit tp
    on pc.tx_permit_id = tp.id
    left join raw_permit rp
    on rp.permit = tp.permit
    and rp.unixtime = tp.issuing_at
ORDER BY tp.issuing_at DESC,tp.permit, sc.x,sc.y
");
            $isSuccess = $getConstruction !== false;
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
            $isSuccess = $getConstruction->execute();
        }
        $bulkConstruction = [];
        if ($isSuccess) {
            $bulkConstruction = $getConstruction->fetchAll(PDO::FETCH_ASSOC);
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


        $this->constructions = new BitrixSection(8, 6);
        $this->permit = new BitrixSection(7, 7);
        foreach ($bulkConstruction as $item) {

            $date = ConvertTimeStamp(time(), 'FULL');
            $fields = array(
                'MODIFIED_BY' => 2,
                'IBLOCK_ID' => $this->constructions->getBlock(),
                'IBLOCK_SECTION_ID' => $this->constructions->getSection(),
                'ACTIVE_FROM' => $date,
                'ACTIVE' => 'Y',
                'NAME' => "{$item['UF_NAME']}",
                'PREVIEW_TEXT' => '',
                'PREVIEW_TEXT_TYPE' => 'text',
                'WF_STATUS_ID' => 1,
                'IN_SECTIONS' => 'Y',
            );

            $element = new CIBlockElement();
            $id = $element->Add($fields);

            $isSuccess = !empty($id);
            if (!$isSuccess) {
                $details = var_export($item, true);
                echo("Fail add element : $details" . PHP_EOL);
            }
            $payload = [];
            if ($isSuccess) {
                $payload = array(
                    'construction_type' => $item['construction'],
                    'address_remark' => $item['remark'],
                    'longitude' => $item['x'],
                    'latitude' => $item['y'],
                );
            }
            $isExists = !empty($item['permit']);
            $response = null;
            if ($isExists) {
                $issuingAt = gmdate('Y-m-d', $item['issuing_at']);
                $response = CIBlockElement::GetList(
                    Array('ID' => 'ASC'),
                    Array('IBLOCK_ID' => $this->permit->getBlock(),
                        'SECTION_ID' => $this->permit->getSection(),
                        'PROPERTY_permit_number' => $item['permit'],
                        'PROPERTY_permit_issuing_at' => $issuingAt,
                    ),
                    false,
                    false,
                    Array('ID')
                );
                $isExists = $response !== false;
            }
            if ($isExists) {
                $identity = $response->Fetch()['ID'];
                $payload['permit_of_ad'] = $identity;
            }
            if ($isSuccess) {
                CIBlockElement::SetPropertyValuesEx($id,
                    $this->constructions->getBlock(),
                    $payload);
            }
        }

        $dbConn->query('SET unique_checks=1');
        $dbConn->query('SET foreign_key_checks=1');
        $dbConn->autocommit(true);
        $dbConn->commit();
    }
}