<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;

use Awz\FlashCallApi as CurrentModule;

Loc::loadMessages(__FILE__);
global $APPLICATION;
$module_id = "awz.flashcallapi";
$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
$zr = "";
if (! ($MODULE_RIGHT >= "R"))
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(Loc::getMessage('AWZ_FLASHCALLAPI_OPT_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

Loader::includeModule($module_id);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $MODULE_RIGHT == "W" && strlen($_REQUEST["Update"]) > 0 && check_bitrix_sessid())
{
    if($_REQUEST['SERV_ADD']!='-'){
        $currentServices = unserialize(Option::get($module_id, 'SERV_ADD', "", ""));
        if(!$currentServices) $currentServices = array();
        $candidate = preg_replace('/([^0-9A-z])/','', $_REQUEST['SERV_ADD']);
        $className = CurrentModule\Helper::DEF_NAMESPACE.$candidate;
        if(class_exists($className) && method_exists($className, 'getName')){
            if(array_search($candidate, $currentServices) === false)
                $currentServices[] = $candidate;
        }
        Option::set($module_id, "SERV_ADD", serialize($currentServices));
    }

    $newServices = array();
    $currentServices = unserialize(Option::get($module_id, 'SERV_ADD', "", ""));
    if(!$currentServices) $currentServices = array();
    foreach($currentServices as $class){
        $className = CurrentModule\Helper::DEF_NAMESPACE.$class;
        if(class_exists($className) && method_exists($className, 'getName')){
            $keyParams = array_search($class.'0', $currentServices);
            $currentValues = array();
            if($keyParams !== false){
                $currentValues = unserialize(Option::get($module_id, 'T_PARAMS_'.$class, "", ""));
                if(!$currentValues) $currentValues = array();
            }

            $params = $className::getConfig();
            foreach($params as $code=>$field){
                $reqCode = $class.'_'.$field->getParameter('inputName');
                $requestValue = Application::getInstance()->getContext()->getRequest()->get($reqCode);
                $resultValue = $field->check($requestValue);
                /* @var \Bitrix\Main\Result $resultValue*/
                if($resultValue->isSuccess()){
                    $valueData = $resultValue->getData();
                    $currentValues[$code] = $valueData['formated'];
                }
            }
            //print_r($currentValues);
            if(Application::getInstance()->getContext()->getRequest()->get($class.'_del')=='Y'){
                Option::set($module_id, 'T_PARAMS_'.$class, '');
            }else{
                $newServices[] = $class;
                Option::set($module_id, 'T_PARAMS_'.$class, serialize($currentValues));
            }
        }
    }
    Option::set($module_id, "SERV_ADD", serialize($newServices));

}

$aTabs = array();

$aTabs[] = array(
    "DIV" => "edit1",
    "TAB" => Loc::getMessage('AWZ_FLASHCALLAPI_OPT_SECT1'),
    "ICON" => "vote_settings",
    "TITLE" => Loc::getMessage('AWZ_FLASHCALLAPI_OPT_SECT1')
);

$aTabs[] = array(
    "DIV" => "edit3",
    "TAB" => Loc::getMessage('AWZ_FLASHCALLAPI_OPT_SECT2'),
    "ICON" => "vote_settings",
    "TITLE" => Loc::getMessage('AWZ_FLASHCALLAPI_OPT_SECT2')
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
    <style>.adm-workarea option:checked {background-color: rgb(206, 206, 206);}</style>
    <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($module_id)?>&lang=<?=LANGUAGE_ID?>&mid_menu=1" id="FORMACTION">

        <?
        $tabControl->BeginNextTab();

        $activeServices = CurrentModule\Helper::getServicesList();
        ?>

        <tr>
            <td><?=Loc::getMessage('AWZ_FLASHCALLAPI_OPT_SERV_ADD')?></td>
            <td>
                <select name="SERV_ADD">
                    <?foreach($activeServices as $code=>$name){?>
                        <option value="<?=$code?>"><?=$name?></option>
                    <?}?>
                </select>
            </td>
        </tr>

        <?
        $currentServices = unserialize(Option::get($module_id, 'SERV_ADD', "", ""));
        if(!$currentServices) $currentServices = array();
        foreach($currentServices as $class){
            $className = CurrentModule\Helper::DEF_NAMESPACE.$class;
            if(class_exists($className) && method_exists($className, 'getName')){
                ?>
                <tr class="heading">
                    <td colspan="2">
                        <?=Loc::getMessage('AWZ_FLASHCALLAPI_OPT_SERV_TITLE', array('#NAME#'=>$className::getName()))?>
                    </td>
                </tr>

                <?

                $keyParams = array_search($class, $currentServices);
                $currentValues = array();
                if($keyParams !== false){
                    $currentValues = unserialize(Option::get($module_id, 'T_PARAMS_'.$class, "", ""));
                    if(!$currentValues) $currentValues = array();
                }

                $params = $className::getConfig();
                $params['dsbl'] = new CurrentModule\Fields\CheckBox(
                    array(
                        'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_OPT_SERV_TITLE_DSBL'),
                        'inputName'=>'dsbl',
                        'inputValue'=>'N',
                        'inputClass'=>'',
                    )
                );
                $params['del'] = new CurrentModule\Fields\CheckBox(
                    array(
                        'title'=>Loc::getMessage('AWZ_FLASHCALLAPI_OPT_SERV_TITLE_DEL'),
                        'inputName'=>'del',
                        'inputValue'=>'N',
                        'inputClass'=>'',
                    )
                );
                ?>

                <?
                /* @var CurrentModule\Fields\BaseField $field*/
                foreach($params as $code=>$field){
                    if(isset($currentValues[$code])){
                        $field->setParameter('inputValue', $currentValues[$code]);
                    }
                    $field->setParameter(
                        'inputName',
                        $class.'_'.$field->getParameter('inputName')
                    );
                    //echo'<pre>';print_r($field);echo'</pre>';
                    ?>
                    <tr>
                        <td><?=$field->getParameter('title');?></td>
                        <td>
                            <?=$field->getHtml();?>
                        </td>
                    </tr>
                <?}?>
                <?
            }
        }
        ?>

        <?
        $tabControl->BeginNextTab();
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
        ?>

        <?
        $tabControl->Buttons();
        ?>
        <input <?if ($MODULE_RIGHT<"W") echo "disabled" ?> type="submit" class="adm-btn-green" name="Update" value="<?=Loc::getMessage('AWZ_FLASHCALLAPI_OPT_L_BTN_SAVE')?>" />
        <input type="hidden" name="Update" value="Y" />
        <?$tabControl->End();?>
    </form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");