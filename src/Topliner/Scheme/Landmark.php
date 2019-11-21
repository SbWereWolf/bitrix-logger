<?php


namespace Topliner\Scheme;


use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Result;
use CDatabase;
use CIBlockElement;
use CIBlockResult;
use CModule;
use CUser;
use Exception;
use LanguageSpecific\ArrayHandler;
use LanguageSpecific\ValueHandler;
use mysqli;

class Landmark
{
    const SECTION_ID = 'SECTION_ID';
    const PUBLISHED_CONSTRUCTS = 9;
    const PUBLISHED_PERMITS = 8;
    const STORE = 'store';
    const ADD_NEW = 'new';
    const PUBLISH = 'publish';
    const FLUSH = 'flush';
    const RESET = 'reset';
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
     * @param $response
     * @return bool
     */
    private static function isRequestSuccess($response)
    {
        $isResult = false;
        if (!empty($response)) {
            $isResult = $response instanceof CIBlockResult;
        }
        $status = false;
        if ($isResult) {
            $status = $response->result !== false;
        }
        if (!$isResult) {
            $status = $response !== false;
        }

        return $status;
    }

    /**
     * @param array $element
     * @param $sectionId
     * @return int
     */
    private static function copyElement(array $element, $sectionId)
    {
        /* @var $USER CUser */
        global $USER;

        $source = $element;
        $userId = (int)$USER->GetID();
        $date = ConvertTimeStamp(time(), 'FULL');

        $element['CREATED_BY'] = $userId;
        $element['IBLOCK_SECTION_ID'] = $sectionId;
        $element['ACTIVE_FROM'] = $date;
        $element['IN_SECTIONS'] = 'Y';
        $copy = (int)(new CIBlockElement())->Add($element);
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
        /* @var $USER CUser */
        global $USER;
        /* @var $DB CDatabase */
        global $DB;

        /** @var $dbConn mysqli */
        $DB->StartTransaction();

        $constructions = (new Reference('ConstructionTypes'))
            ->get();
        $type = '';
        $title = 'Новая рекламная конструкция';
        /* @var $constructions DataManager */
        if (!$constructions !== null) {
            $reference = [];
            try {
                $reference = $constructions::getList(array(
                    'select' => array('UF_NAME', 'UF_XML_ID'),
                    'filter' => array('UF_TYPE_ID' =>
                        $this->parameters->get('type')
                            ->int())
                ));
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            /* @var $name Result */
            $record = $reference->Fetch();
            $type = $record['UF_XML_ID'];
            $title = $record['UF_NAME'];
        }

        $constSec = new BitrixSection(8, 6);

        $userId = $USER->GetID();
        $date = ConvertTimeStamp(time(), 'FULL');
        $fields = array(
            'CREATED_BY' => $userId,
            'MODIFIED_BY' => $userId,
            'IBLOCK_ID' => $constSec->getBlock(),
            'IBLOCK_SECTION_ID' => $constSec->getSection(),
            'ACTIVE_FROM' => $date,
            'ACTIVE' => 'Y',
            'NAME' => $title,
            'PREVIEW_TEXT' => '',
            'PREVIEW_TEXT_TYPE' => 'text',
            'WF_STATUS_ID' => 1,
            'IN_SECTIONS' => 'Y',
        );

        $element = new CIBlockElement();
        $id = $element->Add($fields);
        $isSuccess = !empty($id);
        $fail = false;
        if (!$isSuccess && !$fail) {
            $fail = true;
            $DB->Rollback();
            $details = var_export($fields, true);
            $output['message'] = "Fail add element : $details";
        }
        $payload = [];
        $address = ValueHandler::asUndefined();
        if ($isSuccess) {
            $output['id'] = $id;
            $this->pointId = $output['id'];
            $constructions = (new Reference('ConstructionTypes'))
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
            $constructions = (new Reference('ConstructionTypes'))
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
        /* @var $USER CUser */
        global $USER;
        /* @var $DB CDatabase */
        global $DB;

        /** @var $dbConn mysqli */
        $DB->StartTransaction();

        $fields = array('MODIFIED_BY' => $USER->GetID(),);
        $id = $this->parameters->get('number')->int();
        $element = new CIBlockElement();

        $isSuccess = $element->Update($id, $fields);
        $fail = false;
        if (!$isSuccess) {
            $fail = true;
            $DB->Rollback();
            $output['message'] = 'Fail update construction'
                . var_export($id, true)
                . var_export($fields, true);
        }
        $payload = [];
        $address = ValueHandler::asUndefined();
        if ($isSuccess) {
            $payload = array(
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
            $constSec = new BitrixSection(8, 6);
            CIBlockElement::SetPropertyValuesEx($id,
                $constSec->getBlock(),
                $payload);

            $DB->Commit();
            $isSuccess = $this->writePoints();
        }
        if (!$isSuccess && !$fail) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $fail = true;
            $output['success'] = ['success' => true,
                'message' => 'Success update construct;'
                    . ' Fail update points :'
                    . $this->getPathToPoints()];
        }
        if ($isSuccess) {
            $output = ['success' => true,
                'message' => 'Success update construct;'
                    . ' Success update points;'];
        }

        return $output;
    }

    public function publish()
    {
        $output = ['success' => false, 'message' => 'General error'];
        /* @var $DB CDatabase */
        global $DB;

        /** @var $dbConn mysqli */
        $DB->StartTransaction();

        $identity = $this->parameters->get('number')->int();
        $response = CIBlockElement::GetByID($identity);

        $construct = [];
        $isReadSuccess = !empty($response) && $response->result !== false;
        if (!$isReadSuccess) {
            $output['message'] = 'Fail read construction';
        }
        if ($isReadSuccess) {
            $construct = $response->Fetch();
        }
        $isConstructFound = !empty($construct);
        $isExistsChild = false;
        if ($isConstructFound) {
            Logger::$operation = Logger::CHANGE;
            CIBlockElement::SetPropertyValuesEx(
                $identity, $construct['IBLOCK_ID'],
                [BitrixScheme::PUBLISH_STATUS
                => BitrixScheme::APPROVED]);


            $constructs = BitrixScheme::getPublishedConstructs();
            $filter = ['IBLOCK_ID' => $constructs->getBlock(),
                'SECTION_ID' => $constructs->getSection(),
                'PROPERTY_original' => $identity,
            ];
            $select = ['ID', 'PROPERTY_permit_of_ad'];
            $response = CIBlockElement::GetList([], $filter,
                false, false, $select);
            $isExistsChild = static::isRequestSuccess($response);
        }
        $child = false;
        if ($isExistsChild) {
            $child = $response->Fetch();
        }
        $gotChild = static::isFetchSuccess($child);
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
            /* @var $USER CUser */
            global $USER;

            $source = $construct;
            $userId = (int)$USER->GetID();
            $date = ConvertTimeStamp(time(), 'FULL');

            $construct['CREATED_BY'] = $userId;
            $construct['IBLOCK_SECTION_ID'] = self::PUBLISHED_CONSTRUCTS;
            $construct['ACTIVE_FROM'] = $date;
            $construct['IN_SECTIONS'] = 'Y';
            $published = (new CIBlockElement())->Add($construct);
        }
        $hasCopy = !empty($published);
        if ($isConstructFound && !$hasCopy) {
            $output['message'] = !$letAppend
                ? 'Fail copying of construction;'
                : $output['message'] . ' Fail copying of construction;';
            $letAppend = true;
        }
        $values = [];
        if ($hasCopy) {
            $output['success'] = true;
            $output['message'] = !$letAppend
                ? 'Success copying of construction;'
                : $output['message'] . ' Success copying of construction;';
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
            $publishedPermit = static::copyElement($permit,
                self::PUBLISHED_PERMITS);
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
                $construct['IBLOCK_ID'],
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
        $construct = new Construct();
        $points = $construct->get();
        $json = json_encode($points);
        $file = fopen($this->getPathToPoints(), 'w');
        $isSuccess = $file !== false;

        if ($isSuccess) {
            $isSuccess = fwrite($file, $json);
            fclose($file);
        }

        return ['success' => $isSuccess];
    }

    private function reset()
    {
        $permits = new BitrixSection(7, self::PUBLISHED_PERMITS);
        $constructs = new BitrixSection(8, self::PUBLISHED_CONSTRUCTS);
        $construct = new Construct($permits, $constructs);
        $points = $construct->get();
        $json = json_encode($points);
        $file = fopen($this->getPathToPublished(), 'w');
        $isSuccess = $file !== false;

        if ($isSuccess) {
            $isSuccess = fwrite($file, $json);
            fclose($file);
        }

        return ['success' => $isSuccess];
    }

    /**
     * @param array|false $fetched
     * @return bool
     */
    private static function isFetchSuccess($fetched)
    {
        $status = is_array($fetched);
        if (!$status) {
            $status = $fetched !== false;
        }

        return $status;
    }
}