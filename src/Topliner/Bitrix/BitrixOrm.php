<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

namespace Topliner\Bitrix;


use CIBlockElement;
use CIBlockResult;

class BitrixOrm
{
    const MAX_SIGNED = 9223372036854775807;

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
        $response = CIBlockElement::GetList([current($select) => 'ASC'],
            $filter, false, false, $select);
        $isReadSuccess = static::isRequestSuccess($response);

        if ($isReadSuccess) {
            $response->NavStart(static::MAX_SIGNED);
        }
        $ids = [];
        if (is_array($response->arResult)) {
            $ids = $response->arResult;
            $ids = array_column($ids, current($select));
            $ids = array_map('intval', $ids);
        }

        return $ids;
    }

    /**
     * @param array $forDelete
     * @return bool
     */
    public static function deleteAllOf(array $forDelete)
    {
        $isSuccess = true;
        foreach ($forDelete as $id) {
            $isSuccess = CIBlockElement::Delete($id);
            if (!$isSuccess) {
                break;
            }
        }
        return $isSuccess;
    }
}