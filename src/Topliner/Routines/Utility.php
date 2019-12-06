<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

namespace Topliner\Routines;


use DateTimeImmutable;
use Exception;

class Utility
{
    /**
     * @param $date
     * @return int
     */
    public static function toUnixTime($date)
    {
        $hasDot = strpos($date, '.') !== false;
        $format = 'm/d/Y H:i:s O';
        if ($hasDot) {
            $format = 'd.m.Y H:i:s O';
        }
        try {
            $unixTime = DateTimeImmutable::createFromFormat($format,
                "$date 00:00:00 +00:00")->getTimestamp();
        } catch (Exception $e) {
            $unixTime = 0;
        }

        return $unixTime;
    }
}