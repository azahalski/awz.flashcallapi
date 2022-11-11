<?
namespace Awz\FlashCallApi;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Sender implements Errorable{

    const DEF_TRANSPORT_NAME = "default";

    private $transport = null;

    /** @var ErrorCollection */
    protected $errorCollection;

    function __construct(string $transportName=self::DEF_TRANSPORT_NAME)
    {
        $this->errorCollection = new ErrorCollection();

        $currentServices = unserialize(Option::get(Helper::MODULE_ID, 'SERV_ADD', "", ""));
        if(!$currentServices) $currentServices = array();

        $classCandidates = array();
        $key = array_search($transportName, $currentServices);
        if($key !== false){
            $class = $currentServices[$key];
            $classCandidates[] = $class;
        }elseif($transportName === self::DEF_TRANSPORT_NAME){
            foreach($currentServices as $class){
                if(!in_array($class, $classCandidates))
                    $classCandidates[] = $class;
            }
        }

        $lastTransport = null;
        foreach($classCandidates as $class){
            $className = Helper::DEF_NAMESPACE.$class;
            if(class_exists($className) && method_exists($className, 'getName')){
                $currentClValues = unserialize(Option::get(Helper::MODULE_ID, 'T_PARAMS_'.$class, "", ""));
                if(!$currentClValues) $currentClValues = array();
                if(!$lastTransport){
                    $lastTransport = new $className($currentClValues);
                }else{
                    /* @var TransportBase $lastTransport */
                    if($lastTransport && !$lastTransport->isEnabled()){
                        $lastTransport = new $className($currentClValues);
                    }
                }
                if($lastTransport && $lastTransport->isEnabled()){
                    break;
                }
            }
        }

        $this->setTransport($lastTransport);

    }

    public function send(string $phone, string $code=""): Result
    {

        if(!empty($this->getErrors())){
            $result = new Result();
            $result->addErrors($this->getErrors());
            return $result;
        }
        $result = $this->transport->send($phone, $code);

        /* @var Result $result */
        if($result->isSuccess()){
            $data = $result->getData();
            $addResult = CodesTable::add(array(
                'PHONE'=>$phone,
                'EXT_ID'=>$data['externalId'],
                'CREATE_DATE'=>\Bitrix\Main\Type\DateTime::createFromTimestamp(time()),
                'PRM'=>$data['additionalParams'] ?: array()
            ));
            if($addResult->isSuccess()){
                $result = new Result();
                $result->setData(array(
                    'id'=>$addResult->getId()
                ));
            }else{
                $result->addErrors($addResult->getErrors());
            }
        }

        return $result;
    }

    /**
     * @param int $id
     * @return \Bitrix\Main\Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCode(int $id): Result
    {
        if(!empty($this->getErrors())){
            $result = new Result();
            $result->addErrors($this->getErrors());
            return $result;
        }

        $codesData = CodesTable::getRowById(array('ID'=>$id));

        if(!$codesData){
            $result = new Result();
            $result->addError(new Error(
                Loc::getMessage('AWZ_FLASHCALLAPI_SENDER_NOCODE')
            ));
            return $result;
        }

        return $this->transport->getCode($codesData['PHONE'], $codesData['EXT_ID'], $codesData['PRM']);
    }

    /**
     * @return null|TransportBase
     */
    public function getTransport(): ?TransportBase
    {
        return $this->transport;
    }

    /**
     * @param TransportBase $transport
     * @return Sender
     */
    public function setTransport(TransportBase $transport): Sender
    {
        $this->transport = $transport;
        if(!$transport->isEnabled()){
            $this->addError(new Error(
                Loc::getMessage('AWZ_FLASHCALLAPI_SENDER_DSBL_TRANSPORT')
            ));
        }
        return $this;
    }

    /**
     * Добавление ошибки
     *
     * @param string|Error $message
     * @param int $code
     * @return Sender
     */
    public function addError($message, int $code=0): Sender
    {
        if($message instanceof Error){
            $this->errorCollection[] = $message;
        }elseif(is_string($message)){
            $this->errorCollection[] = new Error($message, $code);
        }
        return $this;
    }

    /**
     * Массив ошибок
     *
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    /**
     * Getting once error with the necessary code.
     *
     * @param string|int $code Code of error.
     * @return Error|null
     */
    public function getErrorByCode($code): ?Error
    {
        return $this->errorCollection->getErrorByCode($code);
    }

}