<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

namespace Topliner\Scheme;

use CDatabase;
use CIBlockElement;
use CModule;
use Exception;
use LanguageSpecific\ArrayHandler;
use mysqli;
use PDO;

class ImportConstruction
{
    const GEOCODER =
        'https://geocode-maps.yandex.ru/1.x/?lang=ru_RU&format=json'
        . '&apikey=344dde82-33ad-407f-b719-4e880eb28ff1&results=1'
        . '&geocode=';

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
     * ImportConstruction constructor.
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
        if ($isSuccess) {
            $command = $connection->exec('SET NAMES \'utf8mb4\''
                . ' COLLATE \'utf8mb4_unicode_ci\'');
            $isSuccess = $command !== false;
        }
        if ($isSuccess) {
            $command = $connection->exec('START TRANSACTION');
            $isSuccess = $command !== false;
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
    join tx_permit_scheme_construction pc
    on sc.uid = pc.tx_scheme_construction_uid
    join tx_permit tp
    on pc.tx_permit_id = tp.id
    join raw_permit rp
    on rp.permit = tp.permit
    and rp.unixtime = tp.issuing_at
WHERE
    pc.checked_construction = 0
UNION
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
    join tx_permit_scheme_construction pc
    on sc.uid = pc.checked_construction
    join tx_permit tp
    on pc.tx_permit_id = tp.id
    join raw_permit rp
    on rp.permit = tp.permit
    and rp.unixtime = tp.issuing_at
WHERE
    pc.checked_construction <> 0
UNION
select
    '' remark, sc1.x, sc1.y,
    ct.UF_XML_ID construction, ct.UF_NAME,
    0 issuing_at, 0 permit
from
    tx_scheme_construction sc1
        join tx_type tt
        on sc1.type = tt.id
        join b_hlbd_construction_types ct
        on ct.UF_XML_ID = tt.code
WHERE
      tt.id <> 0
      and
    NOT EXISTS(select NULL
               from
                   tx_scheme_construction sc
                   join tx_permit_scheme_construction pc
                   on sc.uid = pc.tx_scheme_construction_uid
               WHERE
                     sc.uid = sc1.uid and
                       pc.checked_construction = 0
        )
AND NOT EXISTS(select NULL
               from
               tx_scheme_construction sc
               join tx_permit_scheme_construction pc
               on sc.uid = pc.checked_construction
               WHERE
                       sc.uid = sc1.uid and
               pc.checked_construction <> 0
    )
ORDER BY issuing_at, permit, x, y
");
            $isSuccess = $getConstruction !== false;
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

        $curl = null;
        foreach ($bulkConstruction as $key => $construct) {
            $tryAddress = 0 === (int)$construct['permit'];
            if ($tryAddress && $curl === null) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($curl, CURLOPT_HTTPGET, true);
            }
            $response = null;
            if ($tryAddress) {
                $target = "{$construct['x']},{$construct['y']}";
                curl_setopt($curl, CURLOPT_URL,
                    self::GEOCODER . $target);
                $response = curl_exec($curl);
            }
            $error = '';
            $curlErrorNumber = curl_errno($curl);
            if (!empty($response) && !empty($curlErrorNumber)) {
                $error = curl_error($curl);
                echo PHP_EOL . 'c-url error : ' . PHP_EOL . $error;
            }
            if (!empty($response) && empty($error)) {
                $info = json_decode($response, true);
                $handler = new ArrayHandler($info);
                $bulkConstruction[$key]['remark'] = $handler
                    ->pull('response')
                    ->pull('GeoObjectCollection')
                    ->pull('featureMember')->pull()
                    ->pull('GeoObject')->get('name')->str();
            }
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


        $constructions = BitrixScheme::getConstructs();
        foreach ($bulkConstruction as $item) {

            $date = ConvertTimeStamp(time(), 'FULL');
            $fields = array(
                'IBLOCK_ID' => $constructions->getBlock(),
                'IBLOCK_SECTION_ID' => $constructions->getSection(),
                'ACTIVE_FROM' => $date,
                'NAME' => $item['UF_NAME'],
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
                    'type' => $item['construction'],
                    'remark' => $item['remark'],
                    'longitude' => $item['x'],
                    'latitude' => $item['y'],
                );
            }
            $isExists = !empty($item['permit']);
            $permit = BitrixScheme::getPermits();
            $response = null;
            if ($isExists) {
                $issuingAt = gmdate('Y-m-d', $item['issuing_at']);
                $response = CIBlockElement::GetList(
                    Array('ID' => 'ASC'),
                    Array('IBLOCK_ID' => $permit->getBlock(),
                        'SECTION_ID' => $permit->getSection(),
                        'PROPERTY_number' => $item['permit'],
                        'PROPERTY_issuing_at' => $issuingAt,
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
                    $constructions->getBlock(),
                    $payload);
            }
        }

        $dbConn->query('SET unique_checks=1');
        $dbConn->query('SET foreign_key_checks=1');
        $dbConn->autocommit(true);
        $dbConn->commit();
    }
}