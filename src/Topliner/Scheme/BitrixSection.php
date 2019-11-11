<?php


namespace Topliner\Scheme;


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

    public function __construct($block = 0, $section = 0)
    {
        $this->block = (int)$block;
        $this->section = (int)$section;
    }

    /**
     * @return int
     */
    public function getBlock(): int
    {
        return $this->block;
    }

    /**
     * @return int
     */
    public function getSection(): int
    {
        return $this->section;
    }

}