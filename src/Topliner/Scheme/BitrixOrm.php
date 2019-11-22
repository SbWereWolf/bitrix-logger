<?php


namespace Topliner\Scheme;


use CIBlockResult;

class BitrixOrm
{
    /**
     * @param $response
     * @return bool
     */
    public static function isRequestSuccess($response)
    {
        $isResult = false;
        if (!empty($response)) {
            $isResult = $response instanceof CIBlockResult;
        }
        $status = false;
        if ($isResult) {
            $status = $response->result !== false;
        }
        if (!$isResult) {
            $status = $response !== false;
        }

        return $status;
    }

    /**
     * @param array|false $fetched
     * @return bool
     */
    public static function isFetchSuccess($fetched)
    {
        $status = is_array($fetched);
        if (!$status) {
            $status = $fetched !== false;
        }

        return $status;
    }
}