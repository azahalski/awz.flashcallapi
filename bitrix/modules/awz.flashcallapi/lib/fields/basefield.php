<?php

namespace Awz\FlashCallApi\Fields;

use Bitrix\Main\Result;
use Awz\FlashCallApi\Dict\Parameters;

abstract class BaseField extends Parameters {

    public function getHtml($defaultValue=''): string
    {
        $html = '<input type="text" name="'
            .$this->getParameter('inputName').
            '" value="'
            .$this->getParameter('inputValue',$defaultValue).
            '" class="'
            .$this->getParameter('inputClass').
            '">';
        return $html;
    }

    abstract public function check($value): Result;

}