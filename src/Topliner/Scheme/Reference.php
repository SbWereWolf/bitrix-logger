<?php


namespace Topliner\Scheme;


use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

class Reference
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