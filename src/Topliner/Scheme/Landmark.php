<?php


namespace Topliner\Scheme;


use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Result;
use CDatabase;
use CIBlockElement;
use CModule;
use CUser;
use Exception;
use LanguageSpecific\ArrayHandler;
use LanguageSpecific\ValueHandler;
use mysqli;

class Landmark
{
    /**
     * @var ArrayHandler
     */
    private $parameters;

    public function __construct(ArrayHandler $parameters)
    {
        $this->parameters = $parameters;
    }

    public function process()
    {
        $output = ['success' => false, 'message' => 'Method not found'];
        CModule::IncludeModule(Construct::IBLOCK);
        CModule::IncludeModule('highloadblock');

        $call = $this->parameters->get('call')->str();
        switch ($call) {
            case'new':
                $output = $this->addNew();
                break;
            case'store':
                $output = $this->store();
                break;
            case'publish':
                $output = $this->publish();
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
            /** @noinspection PhpUndefinedMethodInspection */
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
            CIBlockElement::SetPropertyValuesEx($id,
                $constSec->getBlock(),
                $payload);
        }

        if (!$isSuccess && !$fail) {
            $fail = true;
            $DB->Rollback();
            $details = var_export($payload, true);
            $output['message'] = "Fail extend element : $details";
        }

        if ($isSuccess) {
            $output['success'] = true;
            $DB->Commit();
            $isSuccess = $this->writePoints();
        }
        if ($isSuccess) {
            $output['message'] = 'Success update points;';
        }
        if (!$isSuccess && !$fail) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $fail = true;
            $output['message'] = ' Fail update points, path is :'
                . $this->parameters->get('DOCUMENT_ROOT')->str()
                . '/scheme/js/points.js;';
        }

        return $output;
    }

    /**
     * @return bool
     */
    private function writePoints()
    {
        $construct = new Construct();
        $points = $construct->get();
        $json = json_encode($points);
        $json = "var points = $json;";
        $file = fopen($this->parameters->get('DOCUMENT_ROOT')->str()
            . '/scheme/js/points.js', 'w');

        $isSuccess = $file !== false;
        if ($isSuccess) {
            fwrite($file, $json);
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
                    . $this->parameters->get('DOCUMENT_ROOT')->str()
                    . '/scheme/js/points.js;'];
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

        return $output;
    }
}