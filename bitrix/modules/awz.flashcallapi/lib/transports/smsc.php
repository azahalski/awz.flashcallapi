<?php

namespace Awz\FlashCallApi\Transports;

use Awz\FlashCallApi\TransportBase;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class smsc extends TransportBase {

    const API_URL = 'https://smsc.ru/sys/send.php';
    const LANG_CODE_ERR = 'AWZ_FLASHCALLAPI_TRANSPORTS_SMSC_ERR_';

    public function __construct(array $params = array())
    {
        $this->setParameters($params);
        if(!$this->getParameter('api_url')){
            $this->setParameter('api_url', self::API_URL);
        }
        $this->setParameter('len_code', 6);
    }

    public static function getName(): string
    {
        return Loc::getMessage('AWZ_FLASHCALLAPI_TRANSPORTS_SMSC_NAME');
    }

    public static function getConfig(): array
    {
        $fields = parent::getConfig();
        unset($fields['api_key']);
        $fields['login'] = parent::getConfigAdditional('login');
        $fields['psw'] = parent::getConfigAdditional('psw');
        return $fields;
    }

    public function getCode(string $phone, string $externalId, array $additionalParams = array()): Result
    {
        $result = new Result();

        if(!isset($additionalParams['code'])){
            $result->addError(new Error(
                Loc::getMessage('AWZ_FLASHCALLAPI_TRANSPORTS_SMSC_ERR_CODE')
            ));
        }

        $result->setData(array(
            'code'=>$additionalParams['code']
        ));

        return $result;
    }

    public function send(string $phone, string $code): Result
    {

        $phone = preg_replace('/([^0-9])/','', $phone);

        $result = $this->sendRequest(
            $this->getParameter('api_url'),
            HttpClient::HTTP_POST, array(
                'phones'=>$phone,
                'mes'=>'code',
                'call'=>1,
                'login'=>$this->getParameter('login'),
                'psw'=>$this->getParameter('psw'),
                'fmt'=>3,
                'pp'=>$this->getParameter('partner_id', '327698')
            )
        );

        return $result;

    }

    protected function formatResponse($client, string $responseResult): Result
    {
        $result = new Result();

        $checkErrors = $this->checkError($responseResult);
        if(!$checkErrors->isSuccess()){
            return $checkErrors;
        }

        $data = Json::decode($responseResult);

        if(isset($data['id']) && $data['id']){
            $newData = array(
                'original'=>$data,
                'externalId'=>$data['id'],
                'additionalParams'=>array('code'=>$data['code'])
            );
            $result->setData($newData);
        }else{
            $result->addError(new Error(
                Loc::getMessage('AWZ_FLASHCALLAPI_TRANSPORTS_SMSC_ERR_UNKNOWN'), 0
            ));
        }

        return $result;
    }

    private function checkError(string $responseResult): Result
    {
        $result = new Result();

        $message = '';
        $code = 0;
        try{
            $data = Json::decode($responseResult);
            if($data['error'] && $data['error_code']){
                $code = $data['error_code'];
                $message = Loc::getMessage(self::LANG_CODE_ERR.$data['error_code']);
                if(!$message) $message = $data['error'];
                if(!$message) $message = Loc::getMessage('AWZ_FLASHCALLAPI_TRANSPORTS_SMSC_ERR_UNKNOWN');
            }
        }catch (\Exception $e){
            $message = Loc::getMessage('AWZ_FLASHCALLAPI_TRANSPORTS_SMSC_ERR_UNKNOWN');
        }

        if($message){
            $result->addError(new Error(
                $message, $code
            ));
            return $result;
        }

        return $result;
    }

}