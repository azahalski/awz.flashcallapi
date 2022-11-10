<?php

namespace Awz\FlashCallApi\Fields;

use Bitrix\Main\Result;

class Num extends BaseField {

    public function check($value): Result
    {
        $originalValue = $value;
        $value = preg_replace('([^0-9])','',$value);
        $result = new Result();
        $result->setData(array(
            'original'=>$originalValue,
            'formated'=>(int)$value
        ));
        return $result;
    }

}