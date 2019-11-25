<?php


namespace Topliner\Scheme;


use CIBlockElement;

class PermitTab
{
    const LINKS = 'links';

    function OnInit($arArgs)
    {
        $letShow = (int)$arArgs['IBLOCK']['ID']
            === (BitrixScheme::getPermits())->getBlock();
        $result = false;
        if ($letShow) {
            $result = [
                'TABSET' => 'LinksToConstructs',
                'GetTabs' => [static::class, 'GetTabs'],
                'ShowTab' => [static::class, 'ShowTab'],
                'Action' => [static::class, 'Action'],
                'Check' => [static::class, 'Check'],
            ];
        }

        return $result;
    }

    function Action($arArgs)
    {
        return true;
    }

    function Check($arArgs)
    {
        return true;
    }

    function GetTabs($arArgs)
    {
        $arTabs = [
            ['DIV' => self::LINKS,
                'TAB' => 'Рекламные конструкции',
                'TITLE' => 'Рекламные конструкции',
                'ICON' => 'view',],
        ];
        return $arTabs;
    }

    function ShowTab($divName, $arArgs, $bVarsFromForm)
    {
        $isEnable = $divName === self::LINKS;
        $isOrigin = false;
        if ($isEnable) {
            $permits = BitrixScheme::getPermits();
            $isOrigin = (int)($arArgs['IBLOCK']['ID']) ===
                $permits->getBlock();
        }
        $keys = null;
        if ($isEnable && $isOrigin) {
            $keys = BitrixScheme::getConstructs();
        }
        $isPublished = false;
        if ($isEnable && !$isOrigin) {
            $pubPermits = BitrixScheme::getPublishedPermits();
            $isPublished = (int)($arArgs['IBLOCK']['ID']) ===
                $pubPermits->getBlock();
        }
        if ($isEnable && $isPublished) {
            $keys = BitrixScheme::getPublishedConstructs();
        }
        if ($isEnable && ($isOrigin || $isPublished)) {
            $identity = $arArgs['ID'];
            $block = $keys->getBlock();
            $type = $arArgs['IBLOCK_TYPE']['ID'];

            $filter = ['IBLOCK_ID' => $block,
                'SECTION_ID' => $keys->getSection(),
                'PROPERTY_permit_of_ad' => $identity,
            ];
            $select = ['ID', 'NAME', 'PROPERTY_LOCATION',
                'PROPERTY_REMARK',];
            $response = CIBlockElement::GetList([], $filter,
                false, false, $select);

            $rows = [];
            while ($constructs = $response->Fetch()) {
                $id = $constructs['ID'];
                $link = "/bitrix/admin/iblock_element_edit.php"
                    . "?IBLOCK_ID=$block&type=$type&ID=$id";
                $line = ['link' => $link,
                    'id' => $constructs['ID'],
                    'name' => $constructs['NAME'],
                    'location' => $constructs['PROPERTY_LOCATION_VALUE'],
                    'remark' => $constructs['PROPERTY_REMARK_VALUE']
                ];
                $rows[] = $line;
            }
            ?>
            <table class="adm-list-table">
                <thead>
                <tr class="adm-list-table-header">
                    <th class="adm-list-table-cell"
                        style=" padding-left: 14px;">Номер
                    </th>
                    <th class="adm-list-table-cell"
                        style=" padding-left: 14px;">Название
                    </th>
                    <th class="adm-list-table-cell"
                        style=" padding-left: 14px;">Адрес
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($rows as $line) {
                    ?>
                    <tr class="adm-list-table-row">
                        <td class="adm-list-table-cell">
                            <a href="<?= $line['link'] ?>">
                                <?= $line['id'] ?>
                            </a>
                        </td>
                        <td class="adm-list-table-cell">
                            <?= !empty($line['name'])
                                ? $line['name']
                                : $line['id']
                            ?>
                        </td>
                        <td class="adm-list-table-cell">
                            <?= !empty($line['remark'])
                                ? $line['remark']
                                : $line['location']
                            ?>
                        </td>
                    </tr>
                    <?
                }
                ?>
                </tbody>
            </table>
            <?php
        }
    }
}