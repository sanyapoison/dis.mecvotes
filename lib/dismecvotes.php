<?php

namespace Dis\MecVotes;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;


class DisMecVotesTable extends Entity\DataManager
{
    const VoteCheck = 1;

    public static function getTableName()
    {
        return 'dis_mec_votes';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Entity\IntegerField('CONTENT_ID', [
                'required' => true,
            ]),
            new Entity\StringField('CONTENT_TYPE', [
                'required' => true,
            ]),
            new Entity\IntegerField('USER_ID', [
                'required' => true,
            ]),
            new Entity\IntegerField('VOTE', [
                'required' => true,
            ]),
        ];
    }
}
