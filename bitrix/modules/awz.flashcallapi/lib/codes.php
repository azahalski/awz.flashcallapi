<?php

namespace Awz\FlashCallApi;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class CodesTable extends Entity\DataManager
{
    /**
     * @return string
     */
    public static function getFilePath(): string
    {
        return __FILE__;
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'b_awz_flashcallapi_codes';
    }

    /**
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap(): array
    {
        return array(
            new Entity\IntegerField('ID', array(
                    'primary' => true,
                    'autocomplete' => false,
                    'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_CODES_FIELDS_ID')
                )
            ),
            new Entity\StringField('PHONE', array(
                    'required' => true,
                    'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_CODES_FIELDS_PHONE')
                )
            ),
            new Entity\StringField('EXT_ID', array(
                    'required' => true,
                    'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_CODES_FIELDS_EXT_ID')
                )
            ),
            new Entity\DatetimeField('CREATE_DATE', array(
                    'required' => true,
                    'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_CODES_FIELDS_CREATE_DATE')
                )
            ),
            new Entity\StringField('PRM', array(
                    'required' => false,
                    'serialized'=>true,
                    'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_CODES_FIELDS_PRM')
                )
            ),
        );
    }

}