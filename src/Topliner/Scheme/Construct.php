<?php

namespace Topliner\Scheme;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use CFile;
use CIBlockElement;
use CModule;
use Exception;
use LanguageSpecific\ArrayHandler;
use Topliner\Bitrix\BitrixReference;
use Topliner\Bitrix\BitrixSection;
use Topliner\Bitrix\InfoBlock;
use Topliner\Routines\Utility;

class Construct
{
    /**
     * @var BitrixSection
     */
    private $permits = null;
    /**
     * @var BitrixSection
     */
    private $constructs = null;


    public function __construct($permits = null, $constructs = null)
    {
        $isPermit = $permits instanceof BitrixSection;
        if ($isPermit) {
            $this->permits = $permits;
        }
        if (!$isPermit) {
            $this->permits = BitrixScheme::getPermits();
        }
        $isConstruction = $constructs instanceof BitrixSection;
        if ($isConstruction) {
            $this->constructs = $constructs;
        }
        if (!$isConstruction) {
            $this->constructs = BitrixScheme::getConstructs();
        }
    }

    /**
     * @return array
     */
    public function get()
    {

        $filter = [InfoBlock::SECTION_ID => $this->constructs
            ->getSection()];
        $values = [];
        CIBlockElement::GetPropertyValuesArray($values,
            $this->constructs->getBlock(), $filter, [],
            ['GET_RAW_DATA' => 'Y']);

        $permits = [];
        $result = [];
        $constructions = (new BitrixReference('ConstructionTypes'))
            ->get();
        $ofSurfaces = (new BitrixReference('ConstructionFieldType'))
            ->get();
        $ofLightenings = (new BitrixReference('Lightening'))
            ->get();

        $valueIndex = InfoBlock::VALUE;
        foreach ($values as $key => $value) {
            $data = [];
            $source = new ArrayHandler($value);
            $data['title'] = $source
                ->pull('title')->get($valueIndex)->str();

            $properties = static::getConstruction($source, $constructions);

            if (!empty($properties)) {
                $data['construct'] = $properties['id'];
                $data['name'] = $properties['name'];
            }
            $data['location'] = $source
                ->pull('location')->get($valueIndex)->str();
            $data['remark'] = $source
                ->pull('remark')->get($valueIndex)->str();
            $data['x'] = $source
                ->pull('longitude')->get($valueIndex)->double();
            $data['y'] = $source
                ->pull('latitude')->get($valueIndex)->double();
            $data['construct_area'] = $source
                ->pull('construct_area')->get($valueIndex)->str();
            $data['number_of_sides'] = $source
                ->pull('number_of_sides')->get($valueIndex)->str();

            $surface = $source
                ->pull('field_type')->get($valueIndex)->str();
            $data['field_type'] =
                self::getTitleFor($surface, $ofSurfaces);

            $data['construct_height'] = $source
                ->pull('construct_height')->get($valueIndex)
                ->str();
            $data['construct_width'] = $source
                ->pull('construct_width')->get($valueIndex)->str();
            $data['fields_number'] = $source
                ->pull('fields_number')->get($valueIndex)->str();
            $data['fields_area'] = $source
                ->pull('fields_area')->get($valueIndex)->str();
            $images = $source
                ->pull('images')->get($valueIndex)->asIs();
            $data['images'] = [];
            foreach ($images as $image) {
                $data['images'][] = CFile::GetPath($image);
            }
            $lightening = $source
                ->pull('lightening')->get($valueIndex)->str();

            $data['lightening'] =
                self::getTitleFor($lightening, $ofLightenings);


            $permit = $source->pull('permit_of_ad')
                ->get($valueIndex)->int();

            $letSetup = false;
            $isExists = false;
            if ($permit !== 0) {
                $isExists = key_exists($permit, $permits);
                $letSetup = true;
            }

            $data['id'] = $key;

            $original = $source->pull('number')
                ->get($valueIndex)->int();
            $index = $original ?: $key;

            if ($isExists) {
                $permits[$permit][] = $index;
            }
            if ($letSetup && !$isExists) {
                $permits[$permit] = [$index];
            }

            $result[$index] = $data;
        }

        $permitFilter = array_keys($permits);
        $filter = [InfoBlock::SECTION_ID => $this->permits
            ->getSection(), 'ID' => $permitFilter];
        $values = [];
        CIBlockElement::GetPropertyValuesArray($values,
            $this->permits->getBlock(), $filter, [],
            ['GET_RAW_DATA' => 'Y']);

        $permitsInfo = [];

        CModule::IncludeModule('highloadblock');

        $ofDistributors = (new BitrixReference('DistributorsOfAds'))
            ->get();
        foreach ($values as $key => $value) {
            $data = [];
            $source = new ArrayHandler($value);

            $data['number'] = $source
                ->pull('number')->get($valueIndex)->int();
            $data['contract'] = $source
                ->pull('contract')->get($valueIndex)->str();

            $issuingAt = $source->pull('issuing_at')
                ->get($valueIndex)->str();
            $data['issuing_at'] = Utility::toUnixTime($issuingAt);

            $start = $source->pull('start')
                ->get($valueIndex)->str();
            $data['start'] = Utility::toUnixTime($start);

            $finish = $source->pull('finish')
                ->get($valueIndex)->str();
            $data['finish'] = Utility::toUnixTime($finish);

            $distributor = $source
                ->pull('distributor')->get($valueIndex)->str();
            $title = self::getTitleFor($distributor, $ofDistributors);
            $data['distributor'] = $title;

            $permitsInfo[$key] = $data;
        }
        $values = [];

        foreach ($permits as $permitKey => $permit) {
            foreach ($permit as $construction) {
                $result[$construction]['permit']
                    = $permitsInfo[$permitKey];
            }
        }

        return $result;
    }

    /**
     * @param ArrayHandler $source
     * @param DataManager $constructions
     * @return array
     */
    public static function getConstruction(ArrayHandler $source,
                                           $constructions)
    {
        $construction = $source
            ->pull('type')->get(InfoBlock::VALUE)->str();
        $name = null;
        /* @var $constructions DataManager */
        if (!$constructions !== null) {
            /** @noinspection PhpUndefinedMethodInspection */
            try {
                $name = $constructions::getList(array(
                    'select' => array('UF_NAME', 'UF_TYPE_ID'),
                    'filter' => array('UF_XML_ID' => $construction)
                ));
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        $value = [];
        if ($name !== null) {
            /* @var $name Result */
            $data = $name->Fetch();
            $value['id'] = (int)$data['UF_TYPE_ID'];
            $value['name'] = $data['UF_NAME'];
        }

        return $value;
    }

    /**
     * @param string $type
     * @param $constructions
     * @return string
     */
    public static function getConstructionWithType($type,
                                                   $constructions)
    {
        $name = null;
        /* @var $constructions DataManager */
        if (!$constructions !== null) {
            /** @noinspection PhpUndefinedMethodInspection */
            try {
                $name = $constructions::getList(array(
                    'select' => array('UF_NAME'),
                    'filter' => array('UF_TYPE_ID' => $type)
                ));
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        $value = "";
        if ($name !== null) {
            /* @var $name Result */
            $data = $name->Fetch();
            $value = $data['UF_NAME'];
        }

        return $value;
    }

    /**
     * @param string $code
     * @param DataManager $reference
     * @return string
     */
    public static function getTitleFor($code, $reference)
    {
        /* @var $name Result */
        $name = null;
        if (!$reference !== null && !empty($code)) {
            /** @noinspection PhpUndefinedMethodInspection */
            try {
                $name = $reference::getList(array(
                    'select' => array('UF_NAME'),
                    'filter' => array('UF_XML_ID' => $code)
                ));
            } catch (ObjectPropertyException $e) {
                echo $e->getMessage();
            } catch (ArgumentException $e) {
                echo $e->getMessage();
            } catch (SystemException $e) {
                echo $e->getMessage();
            }
        }
        $title = '';
        if ($name !== null) {
            $title = $name->Fetch()['UF_NAME'];
        }
        return $title;
    }
}