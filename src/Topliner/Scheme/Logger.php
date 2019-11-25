<?php


namespace Topliner\Scheme;


use CIBlockElement;
use CIBlockProperty;
use CUser;
use LanguageSpecific\ArrayHandler;
use Topliner\Bitrix\BitrixOrm;
use Topliner\Bitrix\BitrixSection;

class Logger
{
    const UNDEFINED = 'undefined';
    const CHANGE = 'change';
    const REMOVE = 'remove';
    const CREATE = 'create';

    const NO_VALUE = [];
    /**
     * @var array
     */
    private static $fields = self::NO_VALUE;
    /**
     * @var array
     */
    private static $properties = self::NO_VALUE;
    /**
     * @var array
     */
    private static $names = self::NO_VALUE;

    /**
     * @var string
     */
    public static $operation = self::UNDEFINED;

    public function OnAdd(array &$arFields)
    {
        $id = (int)$arFields['ID'];
        $element = static::getBlockAndSection($id);
        $sectionId = $element ? $element['IBLOCK_SECTION_ID'] : 0;
        if ($sectionId !== 0) {
            $arFields['IBLOCK_SECTION_ID'] = $sectionId;
        }

        $fields = new ArrayHandler($arFields);
        list($isAcceptable) = static::isAllow($fields);
        if ($isAcceptable) {
            static::$operation = self::CREATE;
        }
    }

    public static function afterAdd(array &$arFields)
    {
        $id = (int)$arFields['ID'];
        $element = static::getBlockAndSection($id);
        $sectionId = $element ? $element['IBLOCK_SECTION_ID'] : 0;
        if ($sectionId !== 0) {
            $arFields['IBLOCK_SECTION_ID'] = $sectionId;
        }

        $fields = new ArrayHandler($arFields);
        list($isAcceptable, $title) = static::isAllow($fields);

        $itemId = 0;
        $isOk = false;
        if ($isAcceptable) {
            $itemId = $fields->get('ID')->int();
            $isOk = $itemId > 0;
        }

        $audit = BitrixScheme::getAudits();
        $id = 0;
        $date = '';
        $login = '';
        if ($isOk) {
            /* @var $USER CUser */
            global $USER;
            $login = $USER->GetLogin();
            $name = $USER->GetFullName();
            $date = ConvertTimeStamp(time(), 'FULL');
            $record = array(
                'IBLOCK_ID' => $audit->getBlock(),
                'IBLOCK_SECTION_ID' => $audit->getSection(),
                'ACTIVE_FROM' => $date,
                'NAME' => "$name ($login) добавил $title №$itemId",
                'PREVIEW_TEXT' => var_export($arFields, true),
            );

            $element = new CIBlockElement();
            $id = $element->Add($record);
        }
        $isSuccess = !empty($id);
        if ($isSuccess) {
            $payload = array(
                'timestamp' => $date,
                'login' => $login,
                'action' => self::CREATE,
                'subject_id' => $itemId,
                'remark' => "$login добавил $title №$itemId",
                'past' => '',
                'present' => var_export($arFields, true),
            );
            CIBlockElement::SetPropertyValuesEx($id,
                $audit->getBlock(),
                $payload);
        }
    }

    /**
     * @param int $ELEMENT_ID
     * @param int $IBLOCK_ID
     * @param array $PROPERTY_VALUES
     * @param string $PROPERTY_CODE
     * @param array $ar_prop
     * @param array $arDBProps
     */
    public static function OnSetPropertyValues(
        $ELEMENT_ID,
        $IBLOCK_ID,
        array &$PROPERTY_VALUES,
        $PROPERTY_CODE,
        array &$ar_prop,
        array &$arDBProps
    )
    {
        $element = static::getBlockAndSection($ELEMENT_ID);
        $permissible = [
            BitrixScheme::getConstructs(),
            BitrixScheme::getPermits(),
            BitrixScheme::getPublishedConstructs(),
            BitrixScheme::getPublishedPermits()
        ];
        list($isAcceptable) =
            static::shortCheck((int)$element['IBLOCK_ID'],
                $permissible);

        if ($isAcceptable) {
            static::$properties = $arDBProps;
            static::$names = $ar_prop;
        }

    }

    /**
     * @param int $ELEMENT_ID
     * @param int $IBLOCK_ID
     * @param array $PROPERTY_VALUES
     * @param string $PROPERTY_CODE
     */
    public static function afterSetPropertyValues(
        $ELEMENT_ID,
        $IBLOCK_ID,
        array &$PROPERTY_VALUES,
        $PROPERTY_CODE
    )
    {
        $element = static::getBlockAndSection($ELEMENT_ID);

        $permissible = [
            BitrixScheme::getConstructs(),
            BitrixScheme::getPermits(),
            BitrixScheme::getPublishedConstructs(),
            BitrixScheme::getPublishedPermits()
        ];
        list($isAcceptable, $title) =
            static::shortCheck((int)$element['IBLOCK_ID']
                , $permissible);

        $remark = '';
        if ($isAcceptable) {
            $was = new ArrayHandler(static::$properties);
            $after = new ArrayHandler($PROPERTY_VALUES);
            foreach ($PROPERTY_VALUES as $key => $value) {

                $isImages = static::$names[$key]['CODE']
                    === BitrixScheme::IMAGES;
                $isDiffer = false;
                $toBe = '';
                $asIs = '';
                if ($isImages) {
                    $imagesToBe = $after->get($key)->asIs();
                    $imagesAsIs = $was->get($key)->asIs();
                    foreach ($imagesToBe as $fileId => $image) {
                        $export = var_export($image, true);
                        $toBe = "$toBe КАРТИНКА $export; ";
                        if (key_exists($fileId, $imagesAsIs)) {
                            $export = var_export($imagesAsIs[$fileId],
                                true);
                            $asIs = "$asIs КАРТИНКА $export; ";
                        }
                    }
                    $isDiffer = $toBe !== $asIs;
                }
                if (!$isImages && $after->has($key)) {
                    $toBe = $after->pull($key)->pull()
                        ->get('VALUE')->str();
                    $asIs = $was->pull($key)->pull()
                        ->get('VALUE')->str();
                    $isDiffer = ($toBe != $asIs)
                        && !(empty($toBe) && empty($asIs));
                }
                if ($isDiffer) {
                    $name = static::$names[$key]['NAME'];
                    $remark = "$remark `$name` было `$asIs`"
                        . " стало `$toBe`; ";
                }

            }
        }
        $action = 'не известная операция';
        switch (static::$operation) {
            case self::CHANGE:
                $action = 'изменил';
                break;
            case self::REMOVE:
                $action = 'удалил';
                break;
            case self::CREATE:
                $action = 'добавил';
                break;
        }

        $itemId = 0;
        $audit = BitrixScheme::getAudits();
        $id = 0;
        $date = '';
        $login = '';
        $has = !empty($remark);
        if ($has) {
            /* @var $USER CUser */
            global $USER;
            $login = $USER->GetLogin();
            $name = $USER->GetFullName();
            $date = ConvertTimeStamp(time(), 'FULL');
            $itemId = $ELEMENT_ID;
            $record = array(
                'IBLOCK_ID' => $audit->getBlock(),
                'IBLOCK_SECTION_ID' => $audit->getSection(),
                'ACTIVE_FROM' => $date,
                'NAME' => "$name ($login) $action свойства для"
                    . " $title №$itemId",
                'PREVIEW_TEXT' => $remark,
            );

            $element = new CIBlockElement();
            $id = $element->Add($record);
        }
        $isSuccess = !empty($id);
        if ($isSuccess) {
            $payload = array(
                'timestamp' => $date,
                'login' => $login,
                'action' => static::$operation,
                'subject_id' => $itemId,
                'remark' => $remark,
                'past' => var_export(static::$fields, true),
                'present' => var_export($PROPERTY_VALUES, true),
            );
            CIBlockElement::SetPropertyValuesEx($id,
                $audit->getBlock(),
                $payload);
        }
        if ($isAcceptable) {
            static::$properties = self::NO_VALUE;
            static::$names = self::NO_VALUE;
        }
    }

    /**
     * @param int $ELEMENT_ID
     * @param int $IBLOCK_ID
     * @param array $PROPERTY_VALUES
     * @param array $propertyList
     * @param array $arDBProps
     */
    public static function OnSetPropertyValuesEx(
        $ELEMENT_ID,
        $IBLOCK_ID,
        array &$PROPERTY_VALUES,
        array &$propertyList,
        array &$arDBProps
    )
    {
        $element = static::getBlockAndSection($ELEMENT_ID);
        list($isAcceptable) =
            static::fullCheck((int)$element['IBLOCK_ID'],
                (int)$element['IBLOCK_SECTION_ID']);

        if ($isAcceptable) {
            static::$properties = $arDBProps;
            static::$names = $propertyList;
        }
    }

    /**
     * @param int $ELEMENT_ID
     * @param int $IBLOCK_ID
     * @param array $PROPERTY_VALUES
     * @param array $FLAGS
     */
    public static function afterSetPropertyValuesEx(
        $ELEMENT_ID,
        $IBLOCK_ID,
        array &$PROPERTY_VALUES,
        array &$FLAGS
    )
    {
        $element = static::getBlockAndSection($ELEMENT_ID);

        list($isAcceptable, $title) =
            static::fullCheck((int)$element['IBLOCK_ID'],
                (int)$element['IBLOCK_SECTION_ID']);

        $remark = '';
        if ($isAcceptable) {
            $was = new ArrayHandler(static::$properties);
            $after = new ArrayHandler($PROPERTY_VALUES);
            foreach (static::$names as $key => $value) {

                $code = $value['CODE'];
                $isDiffer = false;
                if ($was->has($key) || $after->has($code)) {
                    $isDiffer = ($after->get($code)->str()
                            != $was->pull($key)->pull()->get('VALUE')->str())
                        && !(empty($after->get($code)->str())
                            && empty($was->pull($key)->pull()->get('VALUE')->str()));
                }
                if ($isDiffer) {
                    $name = static::$names[$key]['NAME'];
                    $remark = $remark
                        . "`$name` было "
                        . "`{$was->pull($key)->pull()->get('VALUE')->asIs()}`"
                        . " стало "
                        . "`{$after->get($code)->asIs()}`"
                        . '; ';
                }

            }
        }
        $action = 'не известная операция';
        switch (static::$operation) {
            case self::CHANGE:
                $action = 'изменил';
                break;
            case self::REMOVE:
                $action = 'удалил';
                break;
            case self::CREATE:
                $action = 'добавил';
                break;
        }

        $itemId = 0;
        $audit = BitrixScheme::getAudits();
        $id = 0;
        $date = '';
        $login = '';
        $has = !empty($remark);
        if ($has) {
            /* @var $USER CUser */
            global $USER;
            $login = $USER->GetLogin();
            $name = $USER->GetFullName();
            $date = ConvertTimeStamp(time(), 'FULL');
            $itemId = $ELEMENT_ID;
            $record = array(
                'IBLOCK_ID' => $audit->getBlock(),
                'IBLOCK_SECTION_ID' => $audit->getSection(),
                'ACTIVE_FROM' => $date,
                'NAME' => "$name ($login) $action свойства для"
                    . " $title №$itemId",
                'PREVIEW_TEXT' => $remark,
            );

            $element = new CIBlockElement();
            $id = $element->Add($record);
        }
        $isSuccess = !empty($id);
        if ($isSuccess) {
            $payload = array(
                'timestamp' => $date,
                'login' => $login,
                'action' => static::$operation,
                'subject_id' => $itemId,
                'remark' => $remark,
                'past' => var_export(static::$fields, true),
                'present' => var_export($PROPERTY_VALUES, true),
            );
            CIBlockElement::SetPropertyValuesEx($id,
                $audit->getBlock(),
                $payload);
        }
        if ($isAcceptable) {
            static::$properties = self::NO_VALUE;
            static::$names = self::NO_VALUE;
        }
    }

    public function beforeUpdate(array &$arParams)
    {
        $fields = new ArrayHandler($arParams);
        list($isAcceptable) = static::isAllow($fields);
        $response = false;
        if ($isAcceptable) {
            $filter = ['CODE' => BitrixScheme::PUBLISH_STATUS];
            $response = CIBlockProperty::GetList([], $filter);
        }
        $fetched = false;
        if (!empty($response)) {
            $fetched = $response->Fetch();
        }
        $id = '';
        if (!empty($fetched)) {
            $id = $fetched['ID'];
        }
        $letChange = false;
        $target = '';
        if (!empty($id)) {
            $target = key($arParams['PROPERTY_VALUES'][$id]);
            $letChange =
                $arParams['PROPERTY_VALUES'][$id][$target]['VALUE']
                !== BitrixScheme::APPROVED;
        }
        if ($letChange) {
            $arParams['PROPERTY_VALUES'][$id][$target]['VALUE'] =
                BitrixScheme::DRAFT;
        }
    }


    /*    public function startUpdate(array &$arParams)
            {
                echo '5';
            }
    */

    public static function OnUpdate(array &$newFields, array &$ar_wf_element)
    {
        $id = (int)$newFields['ID'];
        $element = static::getBlockAndSection($id);
        $sectionId = $element ? $element['IBLOCK_SECTION_ID'] : 0;
        if ($sectionId !== 0) {
            $newFields['IBLOCK_SECTION_ID'] = $sectionId;
        }

        $fields = new ArrayHandler($newFields);
        list($isAcceptable) = static::isAllow($fields);
        if ($isAcceptable) {
            static::$operation = self::CHANGE;
            static::$fields = $ar_wf_element;
        }
    }

    public static function afterUpdate(array &$arFields)
    {
        $was = new ArrayHandler(static::$fields);
        $itemId = 0;
        list($isAcceptable, $title) = static::isAllow($was);

        $after = null;
        $isOk = false;
        if ($isAcceptable) {
            $after = new ArrayHandler($arFields);
            $isOk = $after->get('RESULT')->bool();
        }

        $remark = '';
        if ($isOk) {
            foreach ($arFields as $key => $value) {
                $remark = static::writeDifference($key, $after, $was,
                    $remark);
            }
        }
        $audit = BitrixScheme::getAudits();

        $id = 0;
        $date = '';
        $login = '';
        $has = !empty($remark);
        if ($has) {
            /* @var $USER CUser */
            global $USER;
            $login = $USER->GetLogin();
            $name = $USER->GetFullName();
            $date = ConvertTimeStamp(time(), 'FULL');
            $itemId = $was->get('ID')->int();
            $record = array(
                'IBLOCK_ID' => $audit->getBlock(),
                'IBLOCK_SECTION_ID' => $audit->getSection(),
                'ACTIVE_FROM' => $date,
                'NAME' => "$name ($login) изменил $title №$itemId",
                'PREVIEW_TEXT' => $remark,
            );

            $element = new CIBlockElement();
            $id = $element->Add($record);
        }
        $isSuccess = !empty($id);
        if ($isSuccess) {
            $payload = array(
                'timestamp' => $date,
                'login' => $login,
                'action' => static::CHANGE,
                'subject_id' => $itemId,
                'remark' => $remark,
                'past' => var_export(static::$fields, true),
                'present' => var_export($arFields, true),
            );
            CIBlockElement::SetPropertyValuesEx($id,
                $audit->getBlock(),
                $payload);
        }
        if ($isAcceptable) {
            static::$fields = self::NO_VALUE;
        }
    }

    /*
                public static  function OnDelete(int $id)
                {
                    echo '7';
                }
    */
    /**
     * @param int $id
     */
    public static function beforeDelete($id)
    {
        $element = static::getBlockAndSection($id);
        list($isAcceptable) =
            static::fullCheck((int)$element['IBLOCK_ID'],
                (int)$element['IBLOCK_SECTION_ID']);

        if ($isAcceptable) {
            static::$fields = $element;
            static::$operation = self::REMOVE;
        }
    }

    public static function afterDelete(array &$arFields)
    {
        if (!empty(static::$fields)) {
            $arFields['IBLOCK_SECTION_ID']
                = static::$fields['IBLOCK_SECTION_ID'];
        };
        $fields = new ArrayHandler($arFields);
        list($isAcceptable, $title) = static::isAllow($fields);

        $itemId = 0;
        $isOk = false;
        if ($isAcceptable) {
            $itemId = $fields->get('ID')->int();
            $isOk = $itemId > 0;
        }

        $audit = BitrixScheme::getAudits();
        $id = 0;
        $date = '';
        $login = '';
        if ($isOk) {
            /* @var $USER CUser */
            global $USER;
            $login = $USER->GetLogin();
            $name = $USER->GetFullName();
            $date = ConvertTimeStamp(time(), 'FULL');
            $record = array(
                'IBLOCK_ID' => $audit->getBlock(),
                'IBLOCK_SECTION_ID' => $audit->getSection(),
                'ACTIVE_FROM' => $date,
                'NAME' => "$name ($login) удалил $title №$itemId",
                'PREVIEW_TEXT' => var_export($arFields, true),
            );

            $element = new CIBlockElement();
            $id = $element->Add($record);
        }
        $isSuccess = !empty($id);
        if ($isSuccess) {

            $payload = array(
                'timestamp' => $date,
                'login' => $login,
                'action' => self::REMOVE,
                'subject_id' => $itemId,
                'remark' => "$login удалил $title №$itemId",
                'past' => '',
                'present' => var_export($arFields, true),
            );
            CIBlockElement::SetPropertyValuesEx($id,
                $audit->getBlock(),
                $payload);
        }
        if ($isAcceptable) {
            static::$fields = self::NO_VALUE;
        }
    }

    /**
     * @param $id
     * @return array
     */
    private static function getBlockAndSection($id)
    {
        $response = CIBlockElement::GetList(
            array(), array('ID' => $id), false, false,
            array('IBLOCK_ID', 'IBLOCK_SECTION_ID'));
        $element = ['IBLOCK_ID' => 0, 'IBLOCK_SECTION_ID' => 0];
        $isExists = BitrixOrm::isRequestSuccess($response);;
        if ($isExists) {
            $element = $response->Fetch();
        }
        return $element;
    }

    /**
     * @param $key
     * @param ArrayHandler $after
     * @param ArrayHandler $was
     * @param string $remark
     * @return string
     */
    private static function writeDifference(
        $key, ArrayHandler $after, ArrayHandler $was, $remark)
    {
        $isDiffer = false;
        if ($was->has($key) && $after->has($key)) {
            $isDiffer = ($after->get($key)->str()
                    != $was->get($key)->str())
                && !(empty($after->get($key)->asIs())
                    && empty($was->get($key)->asIs()));
        }
        if ($isDiffer) {
            $remark = $remark
                . "`$key` было `{$was->get($key)->asIs()}`"
                . " стало `{$after->get($key)->asIs()}`; ";
        }
        return $remark;
    }

    /**
     * @param ArrayHandler $fields
     * @return array
     */
    private static function isAllow(ArrayHandler $fields)
    {
        $blockId = $fields->get('IBLOCK_ID')->int();
        $sectionId = 0;
        $has = !($fields->pull('IBLOCK_SECTION')->isUndefined());
        if (!$has) {
            $sectionId = $fields->get('IBLOCK_SECTION_ID')->int();
        }
        if ($has) {
            $sectionId = $fields->pull('IBLOCK_SECTION')
                ->get()->int();
        }
        list($isAcceptable, $title) =
            static::fullCheck($blockId, $sectionId);

        return array($isAcceptable, $title);
    }

    /**
     * @param $blockId
     * @param $sectionId
     * @return array
     */
    private static function fullCheck(
        $blockId, $sectionId)
    {
        $permissible = [
            BitrixScheme::getConstructs(),
            BitrixScheme::getPermits(),
            BitrixScheme::getPublishedConstructs(),
            BitrixScheme::getPublishedPermits()
        ];

        list($isAccessible, $title) =
            static::shortCheck($blockId, $permissible);

        if ($isAccessible) {
            $isAccessible = false;
            foreach ($permissible as $item) {
                /* @var $item BitrixSection */
                $isAccessible = $sectionId === $item->getSection();
                if ($isAccessible) {
                    $title = $item->getTitle();
                    break;
                }
            }
        }

        return array($isAccessible, $title);
    }

    /**
     * @param int $blockId
     * @param array $accessible
     * @return array
     */
    private static function shortCheck(
        $blockId, array $accessible)
    {
        $isAccessible = false;
        $title = '';
        foreach ($accessible as $item) {
            /* @var $item BitrixSection */
            $isAccessible = $blockId === $item->getBlock();
            if ($isAccessible) {
                $title = $item->getTitle();
                break;
            }
        }
        return array($isAccessible, $title);
    }
}
