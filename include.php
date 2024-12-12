<?php
defined('B_PROLOG_INCLUDED') || die;

$MODULE_ID = 'dis.mecvotes';

CModule::AddAutoloadClasses(
    $MODULE_ID,
    array(
        "CDisMecVotes" => "classes/general/CDisMecVotes.php",
        "dis\\mecvotes\\dismecvotestable" => "lib/dismecvotes.php",
    )
);
