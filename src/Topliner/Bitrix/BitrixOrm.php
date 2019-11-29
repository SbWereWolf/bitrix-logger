<?php


namespace Topliner\Bitrix;


use CIBlockElement;
use CIBlockResult;

class BitrixOrm
{
    const MAX_UNSIGNED = 18446744073709551615;

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

    /**
     * @param array $filter
     * @return array
     */
    public static function getIdOfAll(array $filter)
    {
        $select = ['ID',];
        $response = CIBlockElement::GetList([], $filter,
            false, false, $select);
        $isReadSuccess = static::isRequestSuccess($response);

        $ids = [];
        if ($isReadSuccess) {
            $response->NavStart(static::MAX_UNSIGNED);
            $ids = $response->arResult;
        }
        if (!empty($ids)) {
            $ids = array_column($ids, 'ID');
        }
        return $ids;
    }
}