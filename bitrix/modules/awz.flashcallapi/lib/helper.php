<?php

namespace Awz\FlashCallApi;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Helper {

    const MODULE_ID = "awz.flashcallapi";
    const DEF_NAMESPACE = '\\Awz\\FlashCallApi\\Transports\\';

    public static function getServicesList(){

        $arServices = array('-'=>Loc::getMessage('AWZ_FLASHCALLAPI_HELPER_DEF'));

        $path = __DIR__.'/transports/';
        foreach(glob($path.'*.php') as $file){
            $class = substr(str_replace($path, '', $file), 0, -4);
            $className = self::DEF_NAMESPACE.$class;
            if(class_exists($className) && method_exists($className, 'getName')){
                $arServices[$class] = $className::getName();
            }
        }

        return $arServices;

    }

}