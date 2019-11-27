<?php


namespace Topliner\Scheme;


use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Result;
use CDatabase;
use CIBlockElement;
use CIBlockSectionRights;
use CModule;
use Exception;
use LanguageSpecific\ArrayHandler;
use LanguageSpecific\ValueHandler;
use mysqli;
use Topliner\Bitrix\BitrixOrm;
use Topliner\Bitrix\BitrixReference;
use Topliner\Bitrix\BitrixSection;

class Landmark
{
    const SECTION_ID = 'SECTION_ID';
    const STORE = 'store';
    const ADD_NEW = 'new';
    const PUBLISH = 'publish';
    const FLUSH = 'flush';
    const RESET = 'reset';
    const ELEMENT_ADD = 'section_element_bind';
    const ELEMENT_EDIT = 'element_edit';
    /**
     * @var ArrayHandler
     */
    private $parameters;

    /**
     * @var int
     */
    private $pointId = 0;

    public function __construct(ArrayHandler $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param array $element
     * @param $section
     * @return int
     */
    private static function copyElement(array $element,
                                        BitrixSection $section)
    {
        $source = $element;
        $date = ConvertTimeStamp(time(), 'FULL');

        $element['IBLOCK_ID'] = $section->getBlock();
        $element['IBLOCK_SECTION_ID'] = $section->getSection();
        $element['ACTIVE_FROM'] = $date;
        $copy = (int)((new CIBlockElement())->Add($element));
        $values = [];
        if (!empty($copy)) {
            $filter = ['ID' => $source['ID'],
                self::SECTION_ID => $source['IBLOCK_SECTION_ID']];
            CIBlockElement::GetPropertyValuesArray($values,
                $source['IBLOCK_ID'], $filter, [],
                ['GET_RAW_DATA' => 'Y']);
        }
        $properties = [];
        if (!empty($values)) {
            $values = current($values);
            foreach ($values as $key => $value) {
                if (!empty($value['VALUE'])) {
                    $properties[$key] = $value['VALUE'];
                }
            }
            $properties[BitrixScheme::PUBLISH_STATUS]
                = BitrixScheme::APPROVED;
        }
        if (!empty($properties)) {
            $currentOp = Logger::$operation;
            Logger::$operation = Logger::CHANGE;
            CIBlockElement::SetPropertyValuesEx($source['ID'],
                $source['IBLOCK_ID'],
                $properties);
            Logger::$operation = $currentOp;
        }
        if (!empty($properties)) {
            $properties['original'] = $source['ID'];
            CIBlockElement::SetPropertyValuesEx($copy,
                $element['IBLOCK_ID'],
                $properties, ['NewElement' => true]);
        }
        return $copy;
    }

    public function process()
    {
        $output = ['success' => false, 'message' => 'Method not found'];
        CModule::IncludeModule(Construct::IBLOCK);
        CModule::IncludeModule('highloadblock');

        $call = $this->parameters->get('call')->str();
        switch ($call) {
            case  self::ADD_NEW:
                $output = $this->addNew();
                break;
            case self::STORE:
                $output = $this->store();
                break;
            case self::PUBLISH:
                $output = $this->publish();
                break;
            case self::FLUSH:
                $output = $this->flush();
                break;
            case self::RESET:
                $output = $this->reset();
                break;
        }

        return $output;
    }

    public function addNew()
    {
        $output = ['success' => false, 'message' => 'General error'];

        $constSec = BitrixScheme::getConstructs();
        $isAllow = CIBlockSectionRights::UserHasRightTo(
            $constSec->getBlock(), $constSec->getSection(),
            self::ELEMENT_ADD, false);
        if (!$isAllow) {
            $output['message'] = 'Forbidden, not enough permission;';
        }

        /* @var $DB CDatabase */
        global $DB;

        $constructions = null;
        if ($isAllow) {
            $DB->StartTransaction();

            $constructions = (new BitrixReference('ConstructionTypes'))
                ->get();
        }
        $type = '';
        $reference = null;
        $append = false;
        /* @var $constructions DataManager */
        if ($constructions !== null) {
            try {
                $reference = $constructions::getList(array(
                    'select' => array('UF_NAME', 'UF_XML_ID'),
                    'filter' => array('UF_TYPE_ID' =>
                        $this->parameters->get('type')
                            ->int())
                ));
            } catch (Exception $e) {
                $append = true;
                $output['message'] = $e->getMessage();
            }
        }
        $id = 0;
        if (!empty($reference)) {

            /* @var $name Result */
            $record = $reference->Fetch();
            $type = $record['UF_XML_ID'];
            $title = $record['UF_NAME'];

            $date = ConvertTimeStamp(time(), 'FULL');

            $fields = array(
                'IBLOCK_ID' => $constSec->getBlock(),
                'IBLOCK_SECTION_ID' => $constSec->getSection(),
                'ACTIVE_FROM' => $date,
                'NAME' => $title,
            );

            $element = new CIBlockElement();
            $id = $element->Add($fields);
        }
        $isSuccess = !empty($id);
        $fail = false;
        if ($isAllow && !$isSuccess && !$fail) {
            $fail = true;
            $DB->Rollback();
            $details = var_export($fields, true);
            $output['message'] = $append
                ? $output['message'] . "Fail add element : $details"
                : "Fail add element : $details";
        }
        $payload = [];
        $address = ValueHandler::asUndefined();
        if ($isSuccess) {
            $output['id'] = $id;
            $this->pointId = $output['id'];
            $constructions = (new BitrixReference('ConstructionTypes'))
                ->get();
            $output['name'] = (new Construct())
                ->getConstructionWithType(
                    $this->parameters->get('type')->str(),
                    $constructions);
            $payload = array(
                'type' => $type,
                'longitude' => $this->parameters->get('x')->double(),
                'latitude' => $this->parameters->get('y')->double(),
            );

            $address = $this->parameters->get('address');
        }
        $location = $address->str();
        if (!empty($location)) {
            $payload['location'] = $location;
        }
        if ($isSuccess) {
            $output['success'] = true;
            $output['message'] = 'Success add new point;';

            $payload[BitrixScheme::PUBLISH_STATUS] = BitrixScheme::DRAFT;

            CIBlockElement::SetPropertyValuesEx($id,
                $constSec->getBlock(),
                $payload, ['NewElement' => true]);
            $DB->Commit();

            $isSuccess = $this->writePoints();
        }
        if ($isSuccess) {
            $output['message'] = $output['message']
                . ' Success update points;';
        }
        if (!$isSuccess && !$fail) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $fail = true;
            $output['message'] = $output['message']
                . ' Fail update points, path is :'
                . $this->getPathToPoints();
        }

        return $output;
    }

    /**
     * @return bool
     */
    private function writePublished()
    {
        $input = $this->parameters;
        $number = $input->get('number')->str();

        $point = [];
        $filename = '';
        $isValid = !empty($number);
        if ($isValid) {
            $filename = $this->getPathToPoints();
            $points = json_decode(file_get_contents($filename), true);
            $point = key_exists($number, $points)
                ? $points[$number] : [];
        }
        $points = [];
        $isExists = !empty($point);
        if ($isExists) {
            $filename = $this->getPathToPublished();
            $points = json_decode(file_get_contents($filename), true);
        }
        if ($isExists && !is_array($points)) {
            $points = [];
        }
        $file = false;
        $json = '';
        if ($isExists) {
            $points[$number] = $point;
            $json = json_encode($points);
            $file = fopen($filename, 'w');
        }
        $isSuccess = $file !== false;
        if ($isSuccess) {
            $isSuccess = fwrite($file, $json) !== false;
            fclose($file);
        }

        return $isSuccess;
    }

    /**
     * @return bool
     */
    private function writePoints()
    {
        $input = $this->parameters;
        $filename = $this->getPathToPoints();

        $call = $input->get('call')->str();
        $number = $input->get('number')->str();
        $isStore = $call === self::STORE;
        $isValid = !empty($number);

        $point = [];
        $points = [];
        if ($isValid && $isStore) {
            $points = json_decode(file_get_contents($filename), true);
            $point = key_exists($number, $points)
                ? $points[$number] : [];
        }
        $found = !empty($point) && $input->get('x')->has();
        $address = $input->get('address')->str();
        if ($found && trim($address)) {
            $point['location'] = trim($address);
        }
        if ($found) {
            $point['x'] = $input->get('x')->double();
            $point['y'] = $input->get('y')->double();
            $points[$number] = $point;
        }
        $isNew = $call === self::ADD_NEW;
        if (!$isValid) {
            $number = (string)$this->pointId;
            $isValid = !empty($number) && $input->get('type')->has();
        }
        if ($isValid && $isNew) {
            $point = [];
            $point['x'] = $input->get('x')->double();
            $point['y'] = $input->get('y')->double();
            $point['location'] = trim($address);

            $type = $input->get('type')->str();
            $constructions = (new BitrixReference('ConstructionTypes'))
                ->get();
            $name = (new Construct())
                ->getConstructionWithType($type, $constructions);
            $point['construct'] = (int)$type;
            $point['name'] = $name;

            $points = json_decode(file_get_contents($filename), true);
            $points[$number] = $point;
        }

        $file = false;
        $json = '';
        if ($isValid) {
            $json = json_encode($points);
            $file = fopen($filename, 'w');
        }

        $isSuccess = $file !== false;
        if ($isSuccess) {
            $isSuccess = fwrite($file, $json) !== false;
            fclose($file);
        }

        return $isSuccess;
    }

    public function store()
    {
        $output = ['success' => false, 'message' => 'General error'];

        $constSec = BitrixScheme::getConstructs();
        $isAllow = CIBlockSectionRights::UserHasRightTo(
            $constSec->getBlock(), $constSec->getSection(),
            self::ELEMENT_EDIT, false);
        if (!$isAllow) {
            $output['message'] = 'Forbidden, not enough permission;';
        }

        $address = $this->parameters->get('address');
        $location = $address->str();
        $payload = [];
        if (!empty($location)) {
            $payload['location'] = $location;
        }

        $isSuccess = false;
        if ($isAllow) {
            $payload['longitude'] = $this->parameters->get('x')->double();
            $payload['latitude'] = $this->parameters->get('y')->double();
            $payload[BitrixScheme::PUBLISH_STATUS] = BitrixScheme::DRAFT;

            $id = $this->parameters->get('number')->int();

            /* @var $DB CDatabase */
            global $DB;
            $DB->StartTransaction();

            Logger::$operation = Logger::CHANGE;
            CIBlockElement::SetPropertyValuesEx($id,
                $constSec->getBlock(),
                $payload);

            $DB->Commit();
            $output = ['success' => true,
                'message' => 'Success update construct;'];

            $isSuccess = $this->writePoints();
        }

        if ($isAllow && !$isSuccess) {
            $output['message'] = $output['message']
                . ' Fail update points :'
                . $this->getPathToPoints();
        }
        if ($isSuccess) {
            $output['message'] = $output['message']
                . ' Success update points;';
        }

        return $output;
    }

    public function publish()
    {
        $output = ['success' => false, 'message' => 'General error'];

        $isAllow = true;
        $pubConstructs = BitrixScheme::getPublishedConstructs();
        $isAllow = $isAllow && CIBlockSectionRights::UserHasRightTo(
                $pubConstructs->getBlock(), $pubConstructs->getSection(),
                self::ELEMENT_ADD, false);

        $pubPermits = BitrixScheme::getPublishedPermits();
        $isAllow = $isAllow && CIBlockSectionRights::UserHasRightTo(
                $pubPermits->getBlock(), $pubPermits->getSection(),
                self::ELEMENT_ADD, false);

        if (!$isAllow) {
            $output['message'] = 'Forbidden, not enough permission;';
        }

        /* @var $DB CDatabase */
        global $DB;

        /** @var $dbConn mysqli */
        $DB->StartTransaction();

        $identity = 0;
        $response = null;
        $isReadSuccess = false;
        if ($isAllow) {
            $identity = $this->parameters->get('number')->int();
            $response = CIBlockElement::GetByID($identity);
            $isReadSuccess = BitrixOrm::isRequestSuccess($response);
        }
        if ($isAllow && !$isReadSuccess) {
            $output['message'] = 'Fail request construction';
        }
        $construct = false;
        if ($isReadSuccess) {
            $construct = $response->Fetch();
        }
        $isConstructFound = BitrixOrm::isFetchSuccess($construct);
        if ($isAllow && $isReadSuccess && !$isConstructFound) {
            $output['message'] = 'Construction not found';
        }
        $isExistsChild = false;
        if ($isConstructFound) {
            Logger::$operation = Logger::CHANGE;
            CIBlockElement::SetPropertyValuesEx(
                $identity, $construct['IBLOCK_ID'],
                [BitrixScheme::PUBLISH_STATUS
                => BitrixScheme::APPROVED]);

            $filter = ['IBLOCK_ID' => $pubConstructs->getBlock(),
                'SECTION_ID' => $pubConstructs->getSection(),
                'PROPERTY_original' => $identity,
            ];
            $select = ['ID', 'PROPERTY_permit_of_ad'];
            $response = CIBlockElement::GetList([], $filter,
                false, false, $select);
            $isExistsChild = BitrixOrm::isRequestSuccess($response);
        }
        $child = false;
        if ($isExistsChild) {
            $child = $response->Fetch();
        }
        $gotChild = BitrixOrm::isFetchSuccess($child);
        $childId = 0;
        $childPermit = 0;
        $gotPublicPermit = false;
        if ($gotChild) {
            $childId = $child['ID'];
            $childPermit = $child['PROPERTY_PERMIT_OF_AD_VALUE'];
            $gotPublicPermit = !empty($childPermit);
        }
        $isSuccessDelete = false;
        if ($gotPublicPermit) {
            $isSuccessDelete = CIBlockElement::Delete($childPermit);
        }
        $letAppend = false;
        if ($gotPublicPermit && !$isSuccessDelete) {
            $output['message'] = 'Fail delete published permit;';
            $letAppend = true;
        }
        if ($gotPublicPermit && $isSuccessDelete) {
            $output['message'] = 'Success delete published permit;';
            $letAppend = true;
        }
        if ($gotChild) {
            $isSuccessDelete = CIBlockElement::Delete($childId);
        }
        if ($gotChild && !$isSuccessDelete) {
            $output['message'] = !$letAppend
                ? 'Fail delete published permit;'
                : $output['message']
                . ' Fail delete published construction;';
            $letAppend = true;
        }
        if ($gotChild && $isSuccessDelete) {
            $output['message'] = !$letAppend
                ? 'Success delete published permit;'
                : $output['message']
                . ' Success delete published construction;';
            $letAppend = true;
        }
        $source = [];
        $published = 0;
        if ($isConstructFound) {
            $source = $construct;

            $section = BitrixScheme::getPublishedConstructs();
            $construct['IBLOCK_ID'] =
                $section->getBlock();
            $construct['IBLOCK_SECTION_ID'] =
                $section->getSection();

            $date = ConvertTimeStamp(time(), 'FULL');
            $construct['ACTIVE_FROM'] = $date;
            $published = (new CIBlockElement())->Add($construct);
        }
        $hasCopy = !empty($published);
        if ($isConstructFound && !$hasCopy) {
            $output['message'] = !$letAppend
                ? 'Fail copying of construction;'
                : $output['message']
                . ' Fail copying of construction;';
            $letAppend = true;
        }
        $values = [];
        if ($hasCopy) {
            $output['success'] = true;
            $output['message'] = !$letAppend
                ? 'Success copying of construction;'
                : $output['message']
                . ' Success copying of construction;';
            $output['published'] = $published;

            $filter = ['ID' => $source['ID'],
                self::SECTION_ID => $source['IBLOCK_SECTION_ID']];
            CIBlockElement::GetPropertyValuesArray($values,
                $source['IBLOCK_ID'], $filter, [],
                ['GET_RAW_DATA' => 'Y']);
        }
        $gotValues = !empty($values);
        if ($hasCopy && !$gotValues) {
            $output['message'] = $output['message']
                . ' Fail read construction properties';
        }
        $properties = [];
        if ($gotValues) {
            $values = current($values);
            foreach ($values as $key => $value) {
                if (!empty($value['VALUE'])) {
                    $properties[$key] = $value['VALUE'];
                }
            }
            $properties[BitrixScheme::PUBLISH_STATUS]
                = BitrixScheme::APPROVED;
            $properties['original'] = $identity;
        }
        $answer = null;
        $hasPermit = !empty($properties)
            && !empty($properties['permit_of_ad']);
        if ($hasPermit) {
            $permitId = (int)$properties['permit_of_ad'];
            $answer = CIBlockElement::GetByID($permitId);
        }
        $hasAnswer = !empty($answer) && $answer->result !== false;
        if ($hasPermit && !$hasAnswer) {
            $output['message'] = $output['message']
                . 'Fail read permit';
        }
        $permit = [];
        if ($hasAnswer) {
            $permit = $answer->Fetch();
        }
        $publishedPermit = 0;
        $gotPermit = !empty($permit) && $permit !== false;
        if ($gotPermit) {
            $publishedPermit = static::copyElement($permit, $pubPermits);
        }
        if ($gotPermit && empty($publishedPermit)) {
            $output['success'] = false;
            $output['message'] = $output['message']
                . 'Fail copying of permit';
        }
        if ($gotPermit && !empty($publishedPermit)) {
            $output['message'] = $output['message'] .
                ' Success copying of permit';
            $output['withPermit'] = $publishedPermit;
        }
        if ($gotPermit && !empty($publishedPermit)) {
            $properties['permit_of_ad'] = $publishedPermit;
        }
        if (!empty($properties)) {
            CIBlockElement::SetPropertyValuesEx($published,
                $pubConstructs->getBlock(),
                $properties, ['NewElement' => true]);
        }

        $isSuccess = $output['success'];
        if (!$isSuccess) {
            $DB->Rollback();
        }
        $writeSuccess = false;
        if ($isSuccess) {
            $DB->Commit();
            $writeSuccess = $this->writePublished();
        }
        if ($isSuccess && !$writeSuccess) {
            $output['message'] = $output['message'] .
                ' Fail update published';
        }
        if ($isSuccess && $writeSuccess) {
            $output['message'] = $output['message'] .
                ' Success update published';
        }

        return $output;
    }

    /**
     * @return string
     */
    private function getPathToPoints()
    {
        return $this->parameters->get('DOCUMENT_ROOT')->str()
            . '/scheme/js/points.json';
    }

    /**
     * @return string
     */
    private function getPathToPublished()
    {
        return $this->parameters->get('DOCUMENT_ROOT')->str()
            . '/scheme/js/published.json';
    }

    private function flush()
    {
        $constSec = BitrixScheme::getConstructs();
        $isAllow = CIBlockSectionRights::UserHasRightTo(
            $constSec->getBlock(), $constSec->getSection(),
            self::ELEMENT_EDIT, false);
        if (!$isAllow) {
            $output['message'] = 'Forbidden, not enough permission;';
        }
        $file = null;
        $json = '';
        $isSuccess = false;
        if ($isAllow) {
            $construct = new Construct();
            $points = $construct->get();
            $json = json_encode($points);
            $file = fopen($this->getPathToPoints(), 'w');
            $isSuccess = $file !== false;
        }
        if ($isSuccess) {
            $isSuccess = fwrite($file, $json) !== false;
            fclose($file);
        }

        return ['success' => $isSuccess];
    }

    private function reset()
    {
        $isAllow = true;
        $pubConstructs = BitrixScheme::getPublishedConstructs();
        $isAllow = $isAllow && CIBlockSectionRights::UserHasRightTo(
                $pubConstructs->getBlock(), $pubConstructs->getSection(),
                self::ELEMENT_ADD, false);

        $pubPermits = BitrixScheme::getPublishedPermits();
        $isAllow = $isAllow && CIBlockSectionRights::UserHasRightTo(
                $pubPermits->getBlock(), $pubPermits->getSection(),
                self::ELEMENT_ADD, false);

        if (!$isAllow) {
            $output['message'] = 'Forbidden, not enough permission;';
        }
        $file = null;
        $json = '';
        $isSuccess = false;
        if ($isAllow) {
            $construct = new Construct($pubPermits, $pubConstructs);
            $points = $construct->get();
            $json = json_encode($points);
            $file = fopen($this->getPathToPublished(), 'w');
            $isSuccess = $file !== false;
        }

        if ($isSuccess) {
            $isSuccess = fwrite($file, $json) !== false;
            fclose($file);
        }

        return ['success' => $isSuccess];
    }

}