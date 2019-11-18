<?php


namespace Topliner\Scheme;


use CIBlockElement;
use CUser;
use LanguageSpecific\ArrayHandler;

class Logger
{
    /**
     * @var ArrayHandler
     */
    private static $before = null;

    /**/
    /*    public function OnAdd(array &$arFields)
        {
        }
    */

    /**/
    public static function afterAdd(array &$arFields)
    {
        $fields = new ArrayHandler($arFields);
        list($isAcceptable, $title) = static::isAllow($fields);

        $itemId = 0;
        $isOk = false;
        if ($isAcceptable) {
            $itemId = $fields->get('ID')->int();
            $isOk = $itemId > 0;
        }

        $audit = new BitrixSection(9, 12, 'аудит');
        $id = 0;
        $date = '';
        $login = '';
        if ($isOk) {
            /* @var $USER CUser */
            global $USER;
            $userId = $USER->GetID();
            $login = $USER->GetLogin();
            $date = ConvertTimeStamp(time(), 'FULL');
            $record = array(
                'CREATED_BY' => $userId,
                'IBLOCK_ID' => $audit->getBlock(),
                'IBLOCK_SECTION_ID' => $audit->getSection(),
                'ACTIVE_FROM' => $date,
                'ACTIVE' => 'Y',
                'NAME' => "$login добавил $title №$itemId",
                'PREVIEW_TEXT' => '',
                'PREVIEW_TEXT_TYPE' => 'text',
                'WF_STATUS_ID' => 1,
                'IN_SECTIONS' => 'Y',
            );

            $element = new CIBlockElement();
            $id = $element->Add($record);
        }
        $isSuccess = !empty($id);
        if ($isSuccess) {

            $payload = array(
                'timestamp' => $date,
                'login' => $login,
                'action' => 'create',
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

    /**/
    /*    public function beforeUpdate(array &$arParams)
        {
            echo '3';
        }*/

    /**/

    /**
     * @param ArrayHandler $fields
     * @return array
     */
    private static function isAllow(ArrayHandler $fields): array
    {
        $permits = new BitrixSection(7, 7, 'Разрешние');
        $constructs = new BitrixSection(8, 6, 'РК');

        $blockId = $fields->get('IBLOCK_ID')->int();
        $sectionId = $fields->pull('IBLOCK_SECTION')->isUndefined();
        if ($sectionId) {
            $sectionId = $fields->get('IBLOCK_SECTION_ID')->int();
        }
        if (!$sectionId) {
            $sectionId = $fields->pull('IBLOCK_SECTION')
                ->get()->int();
        }

        $title = '';
        $isPermit = ($blockId === $permits->getBlock()
            && $sectionId === $permits->getSection());
        if ($isPermit) {
            $title = $permits->getTitle();
        }
        $isConstruct = ($blockId === $constructs->getBlock()
            && $sectionId === $constructs->getSection());
        if ($isConstruct) {
            $title = $constructs->getTitle();
        }
        $isAcceptable = $isPermit || $isConstruct;
        return array($isAcceptable, $title);
    }

    /**/
    /*    public function startUpdate(array &$arParams)
        {
            echo '5';
        }*/

    /**/

    public static function OnUpdate(array &$newFields, array &$ar_wf_element)
    {
        $fields = new ArrayHandler($newFields);
        list($isAcceptable) = static::isAllow($fields);
        if ($isAcceptable) {
            static::$before = $ar_wf_element;
        }
    }
    /*
        public static  function OnDelete(int $id)
        {
            echo '7';
        }

        public static  function beforeDelete(int $id)
        {
            echo '8';
        }

        public static  function afterDelete(array &$arFields)
        {
            echo '9';
        }
    */

    /*
        public static  function OnSetPropertyValues(
            int $ELEMENT_ID,
            int $IBLOCK_ID,
            array &$PROPERTY_VALUES,
            string $PROPERTY_CODE,
            array &$ar_prop,
            array &$arDBProps
        )
        {
            echo '10';
        }


        public static  function afterSetPropertyValues(
            int $ELEMENT_ID,
            int $IBLOCK_ID,
            array &$PROPERTY_VALUES,
            string $PROPERTY_CODE
        )
        {
            echo '11';
        }


        public static  function OnSetPropertyValuesEx(
            int $ELEMENT_ID,
            int $IBLOCK_ID,
            array &$PROPERTY_VALUES,
            array &$propertyList,
            array &$arDBProps
        )
        {
            echo '12';
        }


        public static function afterSetPropertyValuesEx(
            int $ELEMENT_ID,
            int $IBLOCK_ID,
            array &$PROPERTY_VALUES,
            array &$FLAGS
        )
        {
            echo '13';
        }
    */

    public static function afterUpdate(array &$arFields)
    {
        $was = new ArrayHandler(static::$before);
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
            foreach (static::$before as $key => $value) {
                $has = $after->has($key);
                if ($has) {
                    $has = $was->has($key);
                }
                if ($has) {
                    $has = $after->get($key)->str()
                        != $was->get($key)->str();
                }
                if ($has) {
                    $remark = $remark
                        . "`$key` было `{$was->get($key)->asIs()}`"
                        . " стало `{$after->get($key)->asIs()}`; ";
                }
            }
        }
        $audit = new BitrixSection(9, 12, 'аудит');

        $id = 0;
        $date = '';
        $login = '';
        $has = !empty($remark);
        if ($has) {
            /* @var $USER CUser */
            global $USER;
            $userId = $USER->GetID();
            $login = $USER->GetLogin();
            $date = ConvertTimeStamp(time(), 'FULL');
            $itemId = $was->get('ID')->int();
            $record = array(
                'CREATED_BY' => $userId,
                'IBLOCK_ID' => $audit->getBlock(),
                'IBLOCK_SECTION_ID' => $audit->getSection(),
                'ACTIVE_FROM' => $date,
                'ACTIVE' => 'Y',
                'NAME' => "$login изменил $title №$itemId",
                'PREVIEW_TEXT' => '',
                'PREVIEW_TEXT_TYPE' => 'text',
                'WF_STATUS_ID' => 1,
                'IN_SECTIONS' => 'Y',
            );

            $element = new CIBlockElement();
            $id = $element->Add($record);
        }
        $isSuccess = !empty($id);
        if ($isSuccess) {
            $payload = array(
                'timestamp' => $date,
                'login' => $login,
                'action' => 'change',
                'subject_id' => $itemId,
                'remark' => $remark,
                'past' => var_export(static::$before, true),
                'present' => var_export($arFields, true),
            );
            CIBlockElement::SetPropertyValuesEx($id,
                $audit->getBlock(),
                $payload);
        }
    }
}