<?php

namespace Awz\FlashCallApi\Fields;

use Bitrix\Main\Result;

class Text extends BaseField {

    public function check($value): Result
    {
        $result = new Result();
        $result->setData(array(
            'original'=>$value,
            'formated'=>trim($value)
        ));
        return $result;
    }

}