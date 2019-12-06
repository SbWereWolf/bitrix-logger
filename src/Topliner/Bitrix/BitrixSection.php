<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

namespace Topliner\Bitrix;


class BitrixSection
{
    /**
     * @var int
     */
    private $block;
    /**
     * @var int
     */
    private $section;
    /**
     * @var string
     */
    private $item;


    public function __construct($block = 0, $section = 0, $item = '')
    {
        $this->block = (int)$block;
        $this->section = (int)$section;
        $this->item = (string)$item;
    }

    /**
     * @return int
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @return int
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->item;
    }

}