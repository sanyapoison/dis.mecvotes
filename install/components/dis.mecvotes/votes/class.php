<?php

use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Dis\MecVotes\DisMecVotesTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Entity;
use Bitrix\Main\Engine\ActionFilter;

global $USER;

Loc::loadMessages(__FILE__);

class dis_mecvotes_component extends CBitrixComponent implements Controllerable
{
    public $errors = [];

    public function configureActions()
    {
        return [
            'setVote' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_POST
                    ]),
                    new ActionFilter\Csrf()
                ],
            ],
            'getContentStat' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_POST
                    ]),
                    new ActionFilter\Csrf()
                ],
            ],
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        if (!Loader::includeModule('dis.mecvotes')) {
            $this->errors[] = Loc::getMessage('DIS_MEC_VOTES_MODULE_EXISTS');
        }

        if (empty($arParams['ELEMENT_ID'])) {
            $this->errors[] = Loc::getMessage('DIS_MEC_VOTES_PARAM_ELEMENT_ID_EMPTY');
        }

        if (empty($arParams['ENTITY_ID'])) {
            $this->errors[] = Loc::getMessage('DIS_MEC_VOTES_PARAM_ENTITY_ID_EMPTY');
        }

        return parent::onPrepareComponentParams($arParams);
    }

    public function setVoteAction($sSignedParameters)
    {
        global $USER;

        if (!$USER->IsAuthorized()) {
            return false;
        }

        $signer = new ParameterSigner;
        $this->arParams = $signer->unsignParameters($this->__name, $sSignedParameters);
        $this->arParams = $this->onPrepareComponentParams($this->arParams);

        $oResult = $this->setReaction(DisMecVotesTable::VoteCheck, $USER->GetID());
        if ($oResult->isSuccess() === true) {
            global $CACHE_MANAGER;
            $CACHE_MANAGER->ClearByTag("Dis.MecVotes." . $this->arParams['ELEMENT_ID'] . "." . $this->arParams['ENTITY_ID']);
        }
        return $oResult;
    }

    public function setReaction($vote, $userID)
    {

        $oResultGet = DisMecVotesTable::getList([
            'filter' => [
                'CONTENT_ID' => $this->arParams['ELEMENT_ID'],
                'CONTENT_TYPE' => $this->arParams['ENTITY_ID'],
                'USER_ID' => $userID
            ],
            'select' => ['ID', 'VOTE']
        ]);
        if ($oResultGet->getSelectedRowsCount() > 0) {
            $arResultGet = $oResultGet->fetch();
            if ($arResultGet['VOTE'] == $vote) {
                $oResult = DisMecVotesTable::delete($arResultGet['ID']);
            } else {
                $oResult = DisMecVotesTable::update($arResultGet['ID'], ['VOTE' => $vote]);
            }
        } else {
            $oResult = DisMecVotesTable::add([
                'CONTENT_ID' => $this->arParams['ELEMENT_ID'],
                'CONTENT_TYPE' => $this->arParams['ENTITY_ID'],
                'USER_ID' => $userID,
                'VOTE' => $vote
            ]);
        }

        return $oResult;
    }

    public function getContentStatAction($sSignedParameters)
    {
        global $USER;

        $signer = new ParameterSigner;
        $this->arParams = $signer->unsignParameters($this->__name, $sSignedParameters);
        $this->arParams = $this->onPrepareComponentParams($this->arParams);

        $arContentStat = $this->getContentStat($this->arParams['ELEMENT_ID'], $this->arParams['ENTITY_ID'],
            $USER->GetID());

        return [
            'STAT' => $arContentStat,
            'CONTENT_ID' => $this->arParams['ELEMENT_ID'],
            'CONTENT_TYPE' => $this->arParams['ENTITY_ID']
        ];
    }

    protected function getContentStat($elementId, $entityId, $userId = null)
    {
        $arRuntime = [
            new Entity\ExpressionField('COUNT_VOTE',
                'COUNT(IF(`dis_mecvotes_dis_mec_votes`.`VOTE`=' . DisMecVotesTable::VoteCheck . ', `dis_mecvotes_dis_mec_votes`.`VOTE`, NULL))'),
        ];
        $arSelect = ['CONTENT_ID', 'CONTENT_TYPE', 'COUNT_VOTE'];

        if ($userId) {
            $arRuntime[] = new Entity\ExpressionField('IS_VOTED', '(
                    SELECT `dis_mec_votes_user`.`VOTE` FROM `' . DisMecVotesTable::getTableName() . '` AS dis_mec_votes_user WHERE
                    `dis_mec_votes_user`.`CONTENT_TYPE`=`dis_mecvotes_dis_mec_votes`.`CONTENT_TYPE` AND 
                    `dis_mec_votes_user`.`CONTENT_ID`=`dis_mecvotes_dis_mec_votes`.`CONTENT_ID` AND
                    `dis_mec_votes_user`.`USER_ID`=' . new SqlExpression("?i", $userId) . '
                )');
            $arSelect[] = 'IS_VOTED';
        }

        $oResult = DisMecVotesTable::getList([
            'runtime' => $arRuntime,
            'group' => ['CONTENT_ID', 'CONTENT_TYPE'],
            'filter' => [
                'CONTENT_ID' => $elementId,
                'CONTENT_TYPE' => $entityId,
            ],
            'select' => $arSelect
        ]);

        $arContentStat = $oResult->fetchAll();

        if ($userId) {
            foreach ($arContentStat as &$item) {
                if ($item['IS_VOTED'] == DisMecVotesTable::VoteCheck) {
                    $item['IS_VOTED'] = true;
                }
                else{
                    $item['IS_VOTED'] = false;
                }
            }
        }

        return $arContentStat[0];

    }

    public function executeComponent()
    {
        global $USER, $CACHE_MANAGER;

        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                ShowError($error);
            }
            return;
        }

        $iUserId = $USER->GetID();
        $iElementId = $this->arParams['ELEMENT_ID'];
        $iEntityId = $this->arParams['ENTITY_ID'];

        if ($this->StartResultCache()) {
            $relativePath = $CACHE_MANAGER->getCompCachePath($this->getRelativePath());
            $CACHE_MANAGER->StartTagCache($relativePath);
            $CACHE_MANAGER->RegisterTag("Dis.MecVotes." . $iElementId . "." . $iEntityId);
            $this->arResult['STAT'] = $this->getContentStat($iElementId, $iEntityId, $iUserId);
            $CACHE_MANAGER->EndTagCache();
        }

        CJSCore::Init(['ajax', 'json', 'session', 'ui.hint']);

        $this->includeComponentTemplate();
    }

    protected function listKeysSignedParameters()
    {
        return [
            'ELEMENT_ID',
            'ENTITY_ID',
            'CACHE_TYPE',
            'CACHE_TIME',
            'SHOW_COUNTER',
            'COLOR_INVERT'
        ];
    }
}
