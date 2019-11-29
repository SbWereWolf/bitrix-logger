<?php


namespace Topliner\Scheme;


use Topliner\Bitrix\BitrixSection;

class BitrixScheme
{
    const PUBLISH_STATUS = 'publish_status';
    const DRAFT = 'draft';
    const APPROVED = 'approved';
    const IMAGES = 'images';


    /**
     * @return BitrixSection
     */
    public static function getConstructs()
    {
        return new BitrixSection(8, 6, 'РК');
    }

    /**
     * @return BitrixSection
     */
    public static function getPermits()
    {
        return new BitrixSection(7, 7, 'Разрешние');
    }

    /**
     * @return BitrixSection
     */
    public static function getPublishedConstructs()
    {
        return new BitrixSection(11, 14, 'Пуб. РК');
    }

    /**
     * @return BitrixSection
     */
    public static function getPublishedPermits()
    {
        return new BitrixSection(10, 13, 'Пуб. Разрешние');
    }

    /**
     * @return BitrixSection
     */
    public static function getAudits()
    {
        return new BitrixSection(9, 12, 'аудит');
    }
}