<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 18.12.2019 20:17 Volkhin Nikolay
 */

namespace Topliner\Scheme;


use CIBlockElement;
use Topliner\Bitrix\BitrixOrm;
use Topliner\Bitrix\BitrixSection;
use Topliner\Bitrix\InfoBlock;

class Publisher
{
    /**
     * Permits
     *
     * @var BitrixSection
     */
    private $permits;
    /**
     * Constructs
     *
     * @var BitrixSection
     *
     */
    private $constructs;
    /**
     * PublishedPermits
     *
     * @var BitrixSection
     */
    private $pubPermits;
    /**
     * PublishedConstructs
     *
     * @var BitrixSection
     */
    private $pubConstructs;

    public function __construct(
        $permits = null, $constructs = null, $pubPermits = null,
        $pubConstructs = null)
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
        $isPubPermit = $pubPermits instanceof BitrixSection;
        if ($isPubPermit) {
            $this->pubPermits = $pubPermits;
        }
        if (!$isPubPermit) {
            $this->pubPermits = BitrixScheme::getPublishedPermits();
        }
        $isPubConstruction = $pubConstructs instanceof BitrixSection;
        if ($isPubConstruction) {
            $this->pubConstructs = $pubConstructs;
        }
        if (!$isPubConstruction) {
            $this->pubConstructs =
                BitrixScheme::getPublishedConstructs();
        }
    }

    public function publishAll()
    {
        $output = ['success' => false];

        $isSuccess = $this->DeleteNotValid();
        if (!$isSuccess) {
            $output['message'] =
                'Fail remove deleted constructs and permits';
        }

        $unpublished = [];
        if ($isSuccess) {
            $unpublished = $this->searchUnpublished();
        }

        $isSuccess = true;
        foreach ($unpublished as $id) {
            $output = $this->publishOne($id);
            $isSuccess = $output['success'];
            if (!$isSuccess) {
                break;
            }
        }
        if ($isSuccess) {
            $output['success'] = true;
            $numbers = count($unpublished);
            $output['message'] = "Success publish $numbers constructs";
        }
        if ($isSuccess && key_exists('withPermit', $output)) {
            unset($output['withPermit']);
        }

        return $output;
    }

    /**
     * @return array
     */
    public function searchUnpublished()
    {
        $filter = ['IBLOCK_ID' => $this->constructs->getBlock(),
            'SECTION_ID' => $this->constructs->getSection(),
            '!PROPERTY_' . BitrixScheme::PUBLISH_STATUS
            => BitrixScheme::APPROVED,
        ];
        $ids = BitrixOrm::getIdOfAll($filter);

        return $ids;
    }

    /**
     * @param $identity
     * @return array
     */
    public function publishOne($identity)
    {
        $output = ['success' => false];

        $response = CIBlockElement::GetByID($identity);
        $isReadSuccess = BitrixOrm::isRequestSuccess($response);
        if (!$isReadSuccess) {
            $output['message'] = 'Fail request construction';
        }
        $construct = false;
        if ($isReadSuccess) {
            $construct = $response->Fetch();
        }
        $isConstructFound = BitrixOrm::isFetchSuccess($construct);
        if ($isReadSuccess && !$isConstructFound) {
            $output['message'] = 'Construction not found';
        }
        $isExistsChild = false;
        if ($isConstructFound) {
            Logger::$operation = Logger::CHANGE;
            CIBlockElement::SetPropertyValuesEx(
                $identity, $construct['IBLOCK_ID'],
                [BitrixScheme::PUBLISH_STATUS
                => BitrixScheme::APPROVED]);

            $filter = ['IBLOCK_ID' => $this->pubConstructs->getBlock(),
                'SECTION_ID' => $this->pubConstructs->getSection(),
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
                ? 'Fail delete published construction;'
                : $output['message']
                . ' Fail delete published construction;';
            $letAppend = true;
        }
        if ($gotChild && $isSuccessDelete) {
            $output['message'] = !$letAppend
                ? 'Success delete published construction;'
                : $output['message']
                . ' Success delete published construction;';
            $letAppend = true;
        }
        $source = [];
        $published = 0;
        if ($isConstructFound) {
            $source = $construct;

            $construct['IBLOCK_ID'] =
                $this->pubConstructs->getBlock();
            $construct['IBLOCK_SECTION_ID'] =
                $this->pubConstructs->getSection();

            $date = ConvertTimeStamp(time(), 'FULL');
            $construct['ACTIVE_FROM'] = $date;
            unset($construct['ID']);
            unset($construct['TIMESTAMP_X']);
            unset($construct['MODIFIED_BY']);
            unset($construct['DATE_CREATE']);
            unset($construct['CREATED_BY']);
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
                InfoBlock::SECTION_ID => $source['IBLOCK_SECTION_ID']];
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
                $this->pubPermits);
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
        if ($gotPermit && !empty($publishedPermit)
            && !empty($properties)) {
            $properties['permit_of_ad'] = $publishedPermit;
        }
        if (!empty($properties)) {
            CIBlockElement::SetPropertyValuesEx($published,
                $this->pubConstructs->getBlock(),
                $properties, ['NewElement' => true]);
        }
        return $output;
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
        unset($element['ID']);
        unset($element['TIMESTAMP_X']);
        unset($element['MODIFIED_BY']);
        unset($element['DATE_CREATE']);
        unset($element['CREATED_BY']);
        $copy = (int)((new CIBlockElement())->Add($element));
        $values = [];
        if (!empty($copy)) {
            $filter = ['ID' => $source['ID'],
                InfoBlock::SECTION_ID => $source['IBLOCK_SECTION_ID']];
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

    /**
     * @return bool
     */
    private function DeleteNotValid()
    {
        $allFilters = [];

        $allFilters[] = ['IBLOCK_ID' => $this->pubConstructs->getBlock(),
            'SECTION_ID' => $this->pubConstructs->getSection(),
            'PROPERTY_ORIGINAL' => false
        ];
        $allFilters[] = ['IBLOCK_ID' => $this->pubConstructs->getBlock(),
            'SECTION_ID' => $this->pubConstructs->getSection(),
            '!PROPERTY_' . BitrixScheme::PUBLISH_STATUS
            => BitrixScheme::APPROVED,
        ];
        $allFilters[] = ['IBLOCK_ID' => $this->pubPermits->getBlock(),
            'SECTION_ID' => $this->pubPermits->getSection(),
            'PROPERTY_ORIGINAL' => false
        ];
        $allFilters[] = ['IBLOCK_ID' => $this->pubPermits->getBlock(),
            'SECTION_ID' => $this->pubPermits->getSection(),
            '!PROPERTY_' . BitrixScheme::PUBLISH_STATUS
            => BitrixScheme::APPROVED,
        ];

        $allForDelete = [];
        foreach ($allFilters as $filter) {
            $forDelete = BitrixOrm::getIdOfAll($filter);
            $allForDelete = array_merge($allForDelete, $forDelete);
        }

        $isSuccess = BitrixOrm::deleteAllOf($allForDelete);
        return $isSuccess;
    }
}