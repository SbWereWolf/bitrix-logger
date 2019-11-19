<?php

namespace Topliner\Scheme;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\SystemException;
use CIBlockElement;
use CModule;
use Exception;
use LanguageSpecific\ArrayHandler;
use Topliner\Routines\Utility;

class Construct
{

    const IBLOCK = 'iblock';
    const SECTION_ID = 'SECTION_ID';
    const VALUE = 'VALUE';
    /**
     * @var BitrixSection
     */
    private $permits = null;
    /**
     * @var BitrixSection
     */
    private $constructs = null;


    public function __construct($permits = null, $constructions = null)
    {
        $isPermit = $permits instanceof BitrixSection;
        if ($isPermit) {
            $this->permits = $permits;
        }
        if (!$isPermit) {
            $this->permits = new BitrixSection(7, 7);
        }
        $isConstruction = $constructions instanceof BitrixSection;
        if ($isConstruction) {
            $this->constructs = $constructions;
        }
        if (!$isConstruction) {
            $this->constructs = new BitrixSection(8, 6);
        }
    }

    /**
     * @return array
     */
    public function get()
    {
        CModule::IncludeModule(self::IBLOCK);

        $filter = [self::SECTION_ID => $this->constructs
            ->getSection()];
        $values = [];
        CIBlockElement::GetPropertyValuesArray($values,
            $this->constructs->getBlock(), $filter);

        $permits = [];
        $result = [];
        $constructions = (new Reference('ConstructionTypes'))
            ->get();
        $ofSurfaces = (new Reference('ConstructionFieldType'))
            ->get();
        $ofLightenings = (new Reference('Lightening'))
            ->get();
        foreach ($values as $key => $value) {
            $data = [];
            $source = new ArrayHandler($value);

            $data['title'] = $source
                ->pull('title')->get(self::VALUE)->str();

            $properties = self::getConstruction($source, $constructions);
            if (!empty($properties)) {
                $data['construct'] = $properties['id'];
                $data['name'] = $properties['name'];
            }
            $data['location'] = $source
                ->pull('location')->get(self::VALUE)->str();
            $data['remark'] = $source
                ->pull('remark')->get(self::VALUE)->str();
            $data['x'] = $source
                ->pull('longitude')->get(self::VALUE)->double();
            $data['y'] = $source
                ->pull('latitude')->get(self::VALUE)->double();
            $data['construct_area'] = $source
                ->pull('construct_area')->get(self::VALUE)->str();
            $data['number_of_sides'] = $source
                ->pull('number_of_sides')->get(self::VALUE)->str();

            $surface = $source
                ->pull('field_type')->get(self::VALUE)->str();
            $data['field_type'] =
                self::getTitleFor($surface, $ofSurfaces);

            $data['construct_height'] = $source
                ->pull('construct_height')->get(self::VALUE)
                ->str();
            $data['construct_width'] = $source
                ->pull('construct_width')->get(self::VALUE)->str();
            $data['fields_number'] = $source
                ->pull('fields_number')->get(self::VALUE)->str();
            $data['fields_area'] = $source
                ->pull('fields_area')->get(self::VALUE)->str();

            $lightening = $source
                ->pull('lightening')->get(self::VALUE)->str();

            $data['lightening'] =
                self::getTitleFor($lightening, $ofLightenings);


            $permit = $source->pull('permit_of_ad')
                ->get(self::VALUE)->int();

            $letSetup = false;
            $isExists = false;
            if ($permit !== 0) {
                $isExists = key_exists($permit, $permits);
                $letSetup = true;
            }
            if ($isExists) {
                $permits[$permit][] = $key;
            }
            if ($letSetup && !$isExists) {
                $permits[$permit] = [$key];
            }

            $result[(int)$key] = $data;
        }

        $permitFilter = array_keys($permits);
        $filter = [self::SECTION_ID => $this->permits
            ->getSection(), 'ID' => $permitFilter];
        $values = [];
        CIBlockElement::GetPropertyValuesArray($values,
            $this->permits->getBlock(), $filter);

        $permitsInfo = [];

        CModule::IncludeModule('highloadblock');

        $ofDistributors = (new Reference('DistributorsOfAds'))
            ->get();
        foreach ($values as $key => $value) {
            $data = [];
            $source = new ArrayHandler($value);

            $data['number'] = $source
                ->pull('number')->get(self::VALUE)->int();
            $data['contract'] = $source
                ->pull('contract')->get(self::VALUE)->str();

            $issuingAt = $source->pull('issuing_at')
                ->get(self::VALUE)->str();
            $data['issuing_at'] = Utility::toUnixTime($issuingAt);

            $start = $source->pull('start')
                ->get(self::VALUE)->str();
            $data['start'] = Utility::toUnixTime($start);

            $finish = $source->pull('finish')
                ->get(self::VALUE)->str();
            $data['finish'] = Utility::toUnixTime($finish);

            $distributor = $source
                ->pull('distributor')->get(self::VALUE)->str();
            $title = self::getTitleFor($distributor, $ofDistributors);
            $data['distributor'] = $title;

            $permitsInfo[$key] = $data;
        }

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
            ->pull('type')->get(self::VALUE)->str();
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
     * @param string $code
     * @param DataManager $reference
     * @return string
     */
    public static function getTitleFor( $code, $reference)
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