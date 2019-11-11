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

class Construction
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
    private $constructions = null;


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
            $this->constructions = $constructions;
        }
        if (!$isConstruction) {
            $this->constructions = new BitrixSection(8, 6);
        }
    }


    public function extractPoints(): array
    {
        CModule::IncludeModule(self::IBLOCK);

        $filter = [self::SECTION_ID => $this->constructions
            ->getSection()];
        $values = [];
        CIBlockElement::GetPropertyValuesArray($values,
            $this->constructions->getBlock(), $filter);

        $permits = [];
        $result = [];
        $constructions = (new Reference('ConstructionTypes'))
            ->get();
        foreach ($values as $key => $value) {
            $data = [];
            $source = new ArrayHandler($value);

            $data['title'] = $source
                ->pull('construction_title')->get(self::VALUE)->str();

            $properties = $this->getConstruction($source, $constructions);
            if (!empty($properties)) {
                $data['construction'] = $properties['id'];
                $data['name'] = $properties['name'];
            }
            $data['location'] = $source
                ->pull('location_address')->get(self::VALUE)->str();
            $data['remark'] = $source
                ->pull('address_remark')->get(self::VALUE)->str();
            $data['x'] = $source
                ->pull('longitude')->get(self::VALUE)->double();
            $data['y'] = $source
                ->pull('latitude')->get(self::VALUE)->double();
            $data['size'] = $source
                ->pull('information_field_size')->get(self::VALUE)
                ->str();
            $data['type'] = $source
                ->pull('information_field_type')->get(self::VALUE)
                ->str();
            $data['area'] = $source
                ->pull('construction_area_size')->get(self::VALUE)
                ->str();
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

        $distributors = (new Reference('DistributorsOfAds'))
            ->get();
        foreach ($values as $key => $value) {
            $data = [];
            $source = new ArrayHandler($value);

            $data['number'] = $source
                ->pull('permit_number')->get(self::VALUE)->int();

            $issuingAt = $source->pull('permit_issuing_at')
                ->get(self::VALUE)->str();
            $start = $source->pull('permit_start')
                ->get(self::VALUE)->str();
            $finish = $source->pull('permit_finish')
                ->get(self::VALUE)->str();

            $data['issuing_at'] = Utility::toUnixTime($issuingAt);
            $data['start'] = Utility::toUnixTime($start);
            $data['finish'] = Utility::toUnixTime($finish);

            $distributor = $source
                ->pull('ad_distributor')->get(self::VALUE)->str();
            $name = null;
            if (!$distributors !== null) {
                /** @noinspection PhpUndefinedMethodInspection */
                try {
                    $name = $distributors::getList(array(
                        'select' => array('UF_NAME'),
                        'filter' => array('UF_XML_ID' => $distributor)
                    ));
                } catch (ObjectPropertyException $e) {
                    echo $e->getMessage();
                } catch (ArgumentException $e) {
                    echo $e->getMessage();
                } catch (SystemException $e) {
                    echo $e->getMessage();
                }
            }
            if ($name !== null) {
                /* @var $name Result */
                $data['distributor'] = $name->Fetch()['UF_NAME'];
            }

            $properties = $this->getConstruction($source, $constructions);
            if (!empty($properties)) {
                $data['construction'] = $properties['id'];
                $data['name'] = $properties['name'];
            }

            $data['remark'] = $source
                ->pull('address_remark')->get(self::VALUE)->str();
            $data['serial'] = $source
                ->pull('scheme_serial')->get(self::VALUE)->str();
            $data['address'] = $source
                ->pull('location_address')->get(self::VALUE)->str();
            $data['sides'] = $source
                ->pull('construction_side_number')
                ->get(self::VALUE)->str();
            $data['area'] = $source
                ->pull('construction_area_size')->get(self::VALUE)
                ->str();
            $data['height'] = $source
                ->pull('construction_height')->get(self::VALUE)
                ->str();
            $data['width'] = $source
                ->pull('construction_width')->get(self::VALUE)
                ->str();
            $data['types'] = $source
                ->pull('construction_fields_types')
                ->get(self::VALUE)->str();
            $data['numbers'] = $source
                ->pull('construction_fields_number')
                ->get(self::VALUE)->str();

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
    public function getConstruction(ArrayHandler $source,
                                    $constructions): array
    {
        $construction = $source
            ->pull('construction_type')->get(self::VALUE)->str();
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
}