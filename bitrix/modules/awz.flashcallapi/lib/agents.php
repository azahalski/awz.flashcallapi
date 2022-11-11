<?php

namespace Awz\FlashCallApi;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Config\Option;

class Agents {

    public static function agentDeleteOldCodes(): string
    {
        $maxTime = 86400*7;

        $r = CodesTable::getList(
            array(
                'select'=>array('ID'),
                'filter'=>array('<CREATE_DATE'=>DateTime::createFromTimestamp(time()-$maxTime))
            )
        );
        while($data = $r->fetch()){
            CodesTable::delete($data);
        }

        return "\\Awz\\FlashCallApi\\Agents::agentDeleteOldCodes();";
    }

}