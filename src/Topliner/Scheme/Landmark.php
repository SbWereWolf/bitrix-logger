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
use Topliner\Bitrix\BitrixPermission;
use Topliner\Bitrix\BitrixReference;
use Topliner\Bitrix\InfoBlock;

class Landmark
{
    const STORE = 'store';
    const ADD_NEW = 'new';
    const PUBLISH = 'publish';
    const FLUSH = 'flush';
    const RECOMPILE = 'recompile';
    const RELEASE = 'release';
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

    public function process()
    {
        $output = ['success' => false, 'message' => 'Method not found'];
        CModule::IncludeModule(InfoBlock::IBLOCK);
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
                $output = $this->publishOne();
                break;
            case self::RELEASE:
                $output = $this->publishAll();
                break;
            case self::FLUSH:
                $output = $this->flush();
                break;
            case self::RECOMPILE:
                $output = $this->recompile();
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
            BitrixPermission::ELEMENT_ADD, false);
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
        $fields = [];
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
        if ($isAllow && !$isSuccess && !$fail) {
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
            BitrixPermission::ELEMENT_EDIT, false);
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

    public function publishOne()
    {
        $output = ['success' => false, 'message' => 'General error'];

        $isAllow = $this->mayPublish();
        if (!$isAllow) {
            $output['message'] = 'Forbidden, not enough permission;';
        }

        /* @var $DB CDatabase */
        global $DB;

        $identity = $this->parameters->get('number')->int();
        $isSuccess = false;
        if ($isAllow) {
            $DB->StartTransaction();
            $publisher = new Publisher();
            $output = $publisher->publishOne($identity);
            $isSuccess = $output['success'];
        }

        if ($isAllow && !$isSuccess) {
            $DB->Rollback();
        }
        $writeSuccess = false;
        if ($isSuccess) {
            $DB->Commit();
            $writeSuccess = $this->writePublished();
        }
        if ($isSuccess && !$writeSuccess) {
            $output['message'] = $output['message']
                . ' Fail update published';
        }
        if ($isSuccess && $writeSuccess) {
            $output['message'] = $output['message']
                . ' Success update published';
        }

        return $output;
    }

    public function publishAll()
    {
        $output = ['success' => false, 'message' => 'General error'];

        $isAllow = $this->mayPublish();
        if (!$isAllow) {
            $output['message'] = 'Forbidden, not enough permission;';
        }

        /* @var $DB CDatabase */
        global $DB;

        $isSuccess = false;
        if ($isAllow) {
            $DB->StartTransaction();
            $publisher = new Publisher();
            $output = $publisher->publishAll();
            $isSuccess = $output['success'];
        }

        if ($isAllow && !$isSuccess) {
            $DB->Rollback();
        }
        $writeSuccess = false;
        if ($isSuccess) {
            $DB->Commit();
            $writeSuccess = $this->recompile();
        }
        if ($isSuccess && !$writeSuccess) {
            $output['message'] = $output['message']
                . ' Fail update published;';
        }
        if ($isSuccess && $writeSuccess) {
            $output['message'] = $output['message']
                . ' Success update published;';
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
            BitrixPermission::ELEMENT_EDIT, false);
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

    private function recompile()
    {
        $isAllow = $this->mayPublish();
        if (!$isAllow) {
            $output['message'] = 'Forbidden, not enough permission;';
        }
        $file = null;
        $json = '';
        $isSuccess = false;
        if ($isAllow) {
            $pubConstructs = BitrixScheme::getPublishedConstructs();
            $pubPermits = BitrixScheme::getPublishedPermits();
            $construct = new Construct($pubPermits, $pubConstructs);
            $published = $construct->get();
            $json = json_encode($published);
            $file = fopen($this->getPathToPublished(), 'w');
            $isSuccess = $file !== false;
        }

        if ($isSuccess) {
            $isSuccess = fwrite($file, $json) !== false;
            fclose($file);
        }

        return ['success' => $isSuccess];
    }

    /**
     * @return bool
     */
    private function mayPublish()
    {
        $pubConstructs = BitrixScheme::getPublishedConstructs();
        $pubPermits = BitrixScheme::getPublishedPermits();

        $isAllow = true;
        $isAllow = $isAllow && CIBlockSectionRights::UserHasRightTo(
                $pubConstructs->getBlock(), $pubConstructs->getSection(),
                BitrixPermission::ELEMENT_ADD, false);
        $isAllow = $isAllow && CIBlockSectionRights::UserHasRightTo(
                $pubPermits->getBlock(), $pubPermits->getSection(),
                BitrixPermission::ELEMENT_ADD, false);

        return $isAllow;
    }

}