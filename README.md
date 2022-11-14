<!-- desc-start -->
Маркетплейс 1с-Битрикс - https://marketplace.1c-bitrix.ru/solutions/awz.flashcallapi/

## Описание
Модуль содержит API для запроса звонков-кодов.<br>
\* ввод последних цифр номера для подтверждения

## Поддерживаемые шлюзы:
1. [SMSC.RU](https://smsc.ru/?ppmlife&utm_source=a_z&utm_medium=bitrix&utm_campaign=awz_flashcallapi)
2. [SMS.RU](https://mlife.sms.ru/?utm_source=a_z&utm_medium=bitrix&utm_campaign=awz_flashcallapi)

<!-- desc-end -->

## Документация
<!-- dev-start -->
### Awz\FlashCallApi\Sender::__construct

<em>создает объект для работы с API модуля</em>

| Параметр |  | Описание |
| --- | --- | --- |
| $transportName `string` | По умолчанию, первый активный или <br>первый неактивный (если нет активных) | Код транспорта с параметров компонента |

### Awz\FlashCallApi\Sender::send

<em>отправляет запрос на совершение звонка</em> 

| Параметр |  | Описание |
| --- | --- | --- |
| **$phone** `string` | Обязателен | номер телефона <br>на который совершить звонок |
| $code `string` | по умолчанию <br>пустая строка | зарезервировано как доп параметр,<br> обычно не используется на сервисах |

Возвращает `\Bitrix\Main\Result`<br> 
`array` `$result->getData()`

| ключ | описание |
| --- | --- |
| **id** `int` | ид запроса в базе данных на сайте |

#### пример 1

```php
use Bitrix\Main\Loader;
use Awz\FlashCallApi\Sender;

if(Loader::includeModule('awz.flashcallapi')){
    $sender = new Sender();
    $result = $sender->send('79215554433');
    if($result->isSuccess()){
        $dataResult = $result->getData();
        $id = $dataResult['id'];
        if($id){
            $rowData = CodesTable::getRowById();
            print_r($rowData);
        }
    }else{
        print_r($result->getErrorMessages());
    }
}
```

### Awz\FlashCallApi\Sender::getCode

<em>получение кода по идентификатору запроса</em> 

| Параметр |  | Описание |
| --- | --- | --- |
| **$id** `int` | Обязателен | Идентификатор запроса полученный после отправки |

Возвращает `\Bitrix\Main\Result`<br> 
`array` `$result->getData()`

| ключ | описание |
| --- | --- |
| **code** `string` | код (последние цифры номера телефона) |

#### пример 1

```php
use Bitrix\Main\Loader;
use Awz\FlashCallApi\Sender;

$id = 1;

if(Loader::includeModule('awz.flashcallapi')){
    $sender = new Sender();
    $result = $sender->getCode($id);
    if($result->isSuccess()){
        $dataResult = $result->getData();
        //последние цифры номера звонившего
        $code = $dataResult['code']; 
        //проверка кода
        if($code == $_REQUEST['code']){
            //код совпал
        }
    }else{
        print_r($result->getErrorMessages());
    }
}
```

### Awz\FlashCallApi\Sender::getTransport

<em>возвращает текущий установленный транспорт </em><br>
`MyTransport` наследник `\Awz\FlashCallApi\TransportBase`

### Awz\FlashCallApi\Sender::setTransport

<em>установка транспорта минуя параметры модуля </em><br>

<!-- dev-end -->