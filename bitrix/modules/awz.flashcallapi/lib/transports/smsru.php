<?php

namespace Awz\FlashCallApi\Transports;

use Awz\FlashCallApi\TransportBase;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class smsru extends TransportBase {

    const API_URL = 'https://sms.ru/code/call';
    const LANG_CODE_ERR = 'AWZ_FLASHCALLAPI_TRANSPORTS_SMSRU_ERR_';

    public function __construct(array $params = array())
    {
        $this->setParameters($params);
        if(!$this->getParameter('api_url')){
            $this->setParameter('api_url', self::API_URL);
        }
    }

    public static function getName(): string
    {
        return Loc::getMessage('AWZ_FLASHCALLAPI_TRANSPORTS_SMSRU_NAME');
    }

    public function getCode(string $phone, string $externalId, array $additionalParams = array()): Result
    {
        $result = new Result();

        if(!isset($additionalParams['code'])){
            $result->addError(new Error(
                Loc::getMessage('AWZ_FLASHCALLAPI_TRANSPORTS_SMSRU_ERR_CODE')
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
                'phone'=>$phone,
                'ip'=>$this->getParameter('ip', '-1'),
                'api_id'=>$this->getParameter('api_key'),
                'partner_id'=>$this->getParameter('partner_id', '20782')
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

        if(isset($data['status']) && $data['status'] == 'OK'){
            $newData = array(
                'original'=>$data,
                'externalId'=>$data['call_id'],
                'additionalParams'=>array('code'=>$data['code'])
            );
            $result->setData($newData);
        }else{
            $result->addError(new Error(
                Loc::getMessage('AWZ_FLASHCALLAPI_TRANSPORTS_SMSRU_ERR_UNKNOWN'), 0
            ));
        }

        return $result;
    }

    private function checkError(string $responseResult): Result
    {

        $result = new Result();

        $responseResult = trim($responseResult);

        $code = 0;
        if($responseResult === '-1'){
            $code = 1;
        }else{
            if(strlen($responseResult) == strlen(intval($responseResult))){
                $code = (int) $responseResult;
            }
        }

        $message = '';
        if($code){
            $message = Loc::getMessage(self::LANG_CODE_ERR.$code);
            if(!$message){
                $message = Loc::getMessage(self::LANG_CODE_ERR.'0');
            }
            if(!$message) $message = 'unknown error';
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