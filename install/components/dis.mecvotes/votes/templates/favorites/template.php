<?
$strObName = "widgetVote";
$voteType = "favorite";
$arJSParams = array(
    "voteType" => $voteType,
    "isColorInvert" => $arParams['COLOR_INVERT']=='Y',
    "voteId" => $arParams['ELEMENT_ID'],
    "entityId" => $arParams['ENTITY_ID'],
    "isActive" => $arResult['STAT']['IS_VOTED'],
    "countVotes" => (!empty($arResult['STAT']['COUNT_VOTE']) ? $arResult['STAT']['COUNT_VOTE'] : 0),
    "elementId" => $this->getComponent()->getEditAreaId(""),
    "elementSignedParams" => $this->getComponent()->getSignedParameters(),
    "containerId" => "container_". $strObName. "_". $voteType. "_". $arParams['ELEMENT_ID'],
    "buttonId" => "button_". $strObName. "_". $voteType. "_". $arParams['ELEMENT_ID'],
    "counterId" => "counter_". $strObName. "_". $voteType. "_". $arParams['ELEMENT_ID'],
);
?>

<script type="text/javascript">
    <?=$strObName;?>_<?= $arJSParams['voteId']?>_<?= $arJSParams['entityId']?> = new JCDisMecVote(<?=CUtil::PhpToJSObject($arJSParams)?>);
</script>

<div id="<?= $arJSParams['containerId'] ?>" class="votes_bar votes_bar_<?=$arJSParams['voteType']?><?if($arJSParams["isColorInvert"]){echo " color-invert";}?>" data-element="<?= $arJSParams['voteId'] . "," . $arJSParams['entityId'] ?>">
    <div class="vote_action">
        <button
                id="<?= $arJSParams['buttonId'] ?>"
                class="icon-button js-vote-action <?= ($arJSParams['isActive'] ? "is-active" : "") ?>"
                data-eid="<?= $arJSParams['elementId'] ?>"
                onclick="event.preventDefault(); <?=$strObName;?>_<?= $arJSParams['voteId']?>_<?= $arJSParams['entityId']?>.setStateVote()"
        >
            <?=$arJSParams['voteType']?>
        </button>
        <sup id="<?= $arJSParams['counterId'] ?>" class="counter js-vote-counter"<?if($arParams['SHOW_COUNTER'] == 'N'):?> style=" display: none;"<?endif;?>><?= $arJSParams['countVotes'] ?></sup>
    </div>
</div>