<?php

namespace Awz\FlashCallApi\Fields;

use Bitrix\Main\Result;

class CheckBox extends BaseField {

    public function check($value): Result
    {
        $originalValue = $value;
        $result = new Result();
        $result->setData(array(
            'original'=>$originalValue,
            'formated'=>($value === 'Y') ? 'Y' : 'N'
        ));
        return $result;
    }

    public function getHtml($defaultValue='N'): string
    {
        $checked = $this->getParameter('inputValue', $defaultValue) === 'Y' ? ' checked="checked"' : '';

        $html = '<input type="checkbox" name="'
            .$this->getParameter('inputName').
            '" value="Y" class="'
            .$this->getParameter('inputClass').
            '"'.$checked.'>';
        return $html;
    }

}