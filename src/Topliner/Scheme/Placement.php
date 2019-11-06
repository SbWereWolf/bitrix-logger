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
        $getPlace = null;
        if ($isSuccess) {
            $getPlace = $connection->prepare("
select 
    tx.uid, tx.x, tx.y, tx.type, tt.title, 
    coalesce(pr.permit,'') permit,
    coalesce(pr.issuing_at,0) issuing_at,
    coalesce(pr.start,0) start,
    coalesce(pr.finish,0) finish,
    coalesce(pr.address,'') address
from 
    tx_scheme_construction tx
    join tx_type tt on tx.type = tt.id 
    left join tx_permit_scheme_construction tp 
    on tx.uid = tp.tx_scheme_construction_uid
    left join tx_permit pr on tp.tx_permit_id = pr.id
ORDER BY tx.uid
");
            $isSuccess = $getPlace !== false;
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
            $isSuccess = $getPlace->execute();
        }
        $bulkPlaces = [];
        if ($isSuccess) {
            $bulkPlaces = $getPlace->fetchAll(PDO::FETCH_ASSOC);
        }
        if (!empty($connection)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $command = $connection->exec('ROLLBACK');
            $connection = null;
        }
        $points = [];
        foreach ($bulkPlaces as $item) {
            $uid = (int)$item['uid'];
            $isExists = key_exists($uid, $points);
            if (!$isExists) {
                $points[$uid]['x'] = $item['x'];
                $points[$uid]['y'] = $item['y'];
                $points[$uid]['type'] = $item['type'];
                $points[$uid]['title'] = $item['title'];
            }
            $isExists = !empty($item['permit']);
            if ($isExists) {
                $permit = (int)$item['permit'];
                $points[$uid]['permit'][$permit] = [
                    'issuingAt' => (int)$item['issuing_at'],
                    'start' => (int)$item['start'],
                    'finish' => (int)$item['finish'],
                    'address' => $item['address'],
                ];
            }
        }

        return $points;
    }

}