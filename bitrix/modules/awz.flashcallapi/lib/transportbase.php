<?php

namespace Awz\FlashCallApi;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Awz\FlashCallApi\Dict\Parameters;
use Awz\FlashCallApi\Fields;

Loc::loadMessages(__FILE__);

abstract class TransportBase extends Parameters {

    public function __construct(array $params = array())
    {
        $this->setParameters($params);
    }

    public static function getConfig(): array
    {
        return array(
            'timeout'=>new Fields\Num(
                array(
                    'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_TBASE_FIELD_TIMEOUT'),
                    'inputName'=>'timeout',
                    'inputValue'=>'10',
                    'inputClass'=>'',
                )
            ),
            'ssl_check'=>new Fields\CheckBox(
                array(
                    'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_TBASE_FIELD_SSL'),
                    'inputName'=>'ssl_check',
                    'inputValue'=>'N',
                    'inputClass'=>'',
                )
            ),
            'api_key'=>new Fields\Text(
                array(
                    'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_TBASE_FIELD_APIKEY'),
                    'inputName'=>'api_key',
                    'inputValue'=>'',
                    'inputClass'=>'',
                )
            ),
        );
    }

    protected function configHttpClient(): HttpClient
    {

        $client = new HttpClient();

        if((int)$timeout = $this->getParameter('timeout')){
            $client->setTimeout($timeout);
            $client->setStreamTimeout($timeout);
        }

        $sslVerify = $this->getParameter('ssl_check') === 'Y';
        if(!$sslVerify){
            $client->disableSslVerification();
        }

        return $client;

    }

    protected function sendRequest(string $url, string $method=HttpClient::HTTP_GET, array $params=array()): Result
    {
        $result = new Result();
        $client = $this->configHttpClient();

        if($client->query($method, $url, $params)){
            $responseResult = $client->getResult();
        }else{
            $responseResult = false;
        }

        if($responseResult){
            try{
                $result = $this->formatResponse($client, $responseResult);
            }catch (\Exception  $ex){
                $result->addError(
                    new Error($ex->getMessage(), $ex->getCode())
                );
            }
        }else{
            $result->addError(new Error(
                Loc::getMessage('AWZ_FLASHCALLAPI_TBASE_EMP_RESPONSE')
            ));
        }

        return $result;
    }

    /**
     * @param HttpClient $client
     * @param string $responseResult
     * @return Result
     */
    protected function formatResponse(HttpClient $client, string $responseResult): Result
    {
        $result = new Result();
        try{
            $data = Json::decode($responseResult);
            $result->setData($data);
        }catch (Exception $e){
            $result->setData(array(
                'response'=>$responseResult
            ));
        }
        return $result;
    }

    abstract protected function send(string $phone, string $code): Result;
    abstract protected function getCode(string $phone, string $externalId, array $additionalParams): Result;

    abstract public static function getName(): string;

}