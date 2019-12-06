<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

namespace Topliner\Bitrix;


use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

class BitrixReference
{
    private $dictionary = '';

    public function __construct($name)
    {
        $this->dictionary = (string)$name;
    }

    /**
     * @return DataManager|null
     */
    public function get()
    {
        $reference = null;
        try {
            $entity = HighloadBlockTable
                ::compileEntity($this->dictionary);
            $reference = $entity->getDataClass();
        } catch (SystemException $e) {
            echo $e->getMessage();
        }
        return $reference;
    }
}