<?php


namespace Topliner\Scheme;


class BitrixScheme
{
    const PUBLISH_STATUS = 'publish_status';
    const DRAFT = 'draft';
    const APPROVED = 'approved';

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
        return new BitrixSection(8, 9, 'Пуб. РК');
    }

    /**
     * @return BitrixSection
     */
    public static function getPublishedPermits()
    {
        return new BitrixSection(7, 8, 'Пуб. Разрешние');
    }
}