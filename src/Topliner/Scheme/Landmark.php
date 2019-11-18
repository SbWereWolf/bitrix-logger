<?php


namespace Topliner\Scheme;


use Bitrix\Main\ORM\Data\DataManager;
use CDatabase;
use CIBlockElement;
use CUser;
use Exception;
use LanguageSpecific\ArrayHandler;
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

        $call = $this->parameters->get('call');
        switch ($call) {
            case'new':
                $output = $this->new();
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

    public function new()
    {
        $output = ['success' => false, 'message' => 'General error'];
        /* @var $USER CUser */
        global $USER;
        /* @var $DB CDatabase */
        global $DB;

        /** @var $dbConn mysqli */
        $dbConn = $DB->db_Conn;
        $dbConn->begin_transaction();

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
            $type = $reference['UF_XML_ID'];
            $title = $reference['UF_NAME'];
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
            $dbConn->rollback();
            $details = var_export($fields, true);
            $output['message'] = "Fail add element : $details";
        }
        $payload = [];
        if ($isSuccess) {
            $output['id'] = $id;

            $payload = array(
                'construct' => $type,
                'longitude' => $this->parameters->get('x')->double(),
                'latitude' => $this->parameters->get('y')->double(),
            );
            CIBlockElement::SetPropertyValuesEx($id,
                $constSec->getBlock(),
                $payload);
        }

        if (!$isSuccess && !$fail) {
            $fail = true;
            $dbConn->rollback();
            $details = var_export($payload, true);
            $output['message'] = "Fail extend element : $details";
        }

        if ($isSuccess) {
            $dbConn->commit();
            $isSuccess = $this->writePoints();
        }
        if ($isSuccess) {
            $output = ['success' => true,
                'message' => 'Success add new construct;'
                    . ' Success update points;'];
        }
        if (!$isSuccess && !$fail) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $fail = true;
            $output = ['success' => true,
                'message' => 'Success add new construct;'
                    . ' Fail update points;'];
        }

        return $output;
    }

    private function writePoints(): bool
    {
        $construct = new Construct();
        $points = $construct->get();
        $json = json_encode($points);
        $json = "var points = $json;";
        $file = fopen(realpath(__DIR__) . '/js/points.js', 'w');

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
        $dbConn = $DB->db_Conn;
        $dbConn->begin_transaction();

        $fields = array('MODIFIED_BY' => $USER->GetID(),);
        $id = $this->parameters->get('number')->int();
        $element = new CIBlockElement();

        $isSuccess = $element->Update($id, $fields);
        $fail = false;
        if (!$isSuccess) {
            $fail = true;
            $dbConn->rollback();
            $output['message'] = 'Fail update construction';
        }
        if ($isSuccess) {
            $payload = array(
                'longitude' => $this->parameters->get('x')->double(),
                'latitude' => $this->parameters->get('y')->double(),
            );
            $constSec = new BitrixSection(8, 6);

            CIBlockElement::SetPropertyValuesEx($id,
                $constSec->getBlock(),
                $payload);

            $dbConn->commit();
            $isSuccess = $this->writePoints();
        }
        if (!$isSuccess && !$fail) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $fail = true;
            $output = ['success' => true,
                'message' => 'Success update construct;'
                    . ' Fail update points;'];
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