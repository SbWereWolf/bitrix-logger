<?php

namespace Topliner\Scheme;

use Exception;
use PDO;

class Placement
{
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

    public function __construct(string $type, string $host, string $base,
                                string $login, string $password)
    {
        $this->type = $type;
        $this->host = $host;
        $this->base = $base;
        $this->login = $login;
        $this->password = $password;
    }

    public function getPoints(): array
    {
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
        $permit = null;
        if ($connection !== null) {
            $permit = $connection->prepare('
select 
       pt.id, pt.longitude,pt.latitude,
       tp.tx_scheme_construction_uid uid,tp.distance,tp.allowance
from 
    tx_permit pt
    left join tx_permit_scheme_construction tp 
    on pt.id = tp.tx_permit_id
ORDER BY uid, pt.id
');
            $isSuccess = $permit !== false;
        }
        $place = null;
        if ($isSuccess) {
            $place = $connection->prepare('
select 
       tx.uid, tx.x,tx.y,tp.distance,tp.allowance
from 
     tx_scheme_construction tx
    left join tx_permit_scheme_construction tp 
    on tx.uid = tp.tx_scheme_construction_uid
ORDER BY tx.uid
');
            $isSuccess = $permit !== false;
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
            $isSuccess = $permit->execute();
        }
        $bulkPermit = [];
        if ($isSuccess) {
            $bulkPermit = $permit->fetchAll(PDO::FETCH_ASSOC);
            $isSuccess = $place->execute();
        }
        $bulkPlaces = [];
        if ($isSuccess) {
            $bulkPlaces = $place->fetchAll(PDO::FETCH_ASSOC);
        }
        if (!empty($connection)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $command = $connection->exec('ROLLBACK');
            $connection = null;
        }
        $may = !empty($bulkPermit) || !empty($bulkPlaces);
        $location = ['permit' => [], 'place' => []];
        if ($may) {
            foreach ($bulkPermit as $item) {

                $isPermit = isset($location['permit'][$item['id']]);
                if (!$isPermit) {
                    $location['permit'][$item['id']] =
                        ['x' => $item['longitude'],
                            'y' => $item['latitude']];
                }
                $isLocation = !empty($item['uid']);
                if ($isLocation) {
                    $distance = $item['distance'];
                    $allowance = $item['allowance'];

                    $location['permit'][$item['id']]
                    ['place'][$item['uid']]['distance'] = $distance;
                    $location['permit'][$item['id']]
                    ['place'][$item['uid']]['allowance'] = $allowance;

                    $location['place'][$item['uid']]
                    ['permit'][$item['id']]['distance'] =
                        $distance;
                    $location['place'][$item['uid']]
                    ['permit'][$item['id']]['allowance'] =
                        $allowance;
                }
            }
            $bulkPermit = null;
            foreach ($bulkPlaces as $item) {
                $isPlace = isset($location['place'][$item['uid']])
                    && isset($location['place'][$item['uid']]['x']);
                if (!$isPlace) {
                    $location['place'][$item['uid']]['x'] = $item['x'];
                    $location['place'][$item['uid']]['y'] = $item['y'];
                }
            }
            $bulkPlaces = null;
        }

        return $location;
    }

}