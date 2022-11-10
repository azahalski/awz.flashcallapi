<?
namespace Awz\FlashCallApi;

use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

Loc::loadMessages(__FILE__);

class Sender implements Errorable{

    const DEF_TRANSPORT_NAME = "default";

    private $transport = null;

    /** @var ErrorCollection */
    protected $errorCollection;

    function __construct(string $transportName=self::DEF_TRANSPORT_NAME)
    {
        $this->errorCollection = new ErrorCollection();
    }

    public function send(string $phone, string $code=""): Result
    {
        if(!empty($this->getErrors())){
            $result = new Result();
            $result->addErrors($this->getErrors());
            return $result;
        }
        $result = $this->transport->send($phone, $code);

        return $result;
    }

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