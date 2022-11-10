<?php
use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\EventManager,
    \Bitrix\Main\ModuleManager,
    \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class awz_flashcallapi extends CModule {

    var $MODULE_ID = "awz.flashcallapi";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";

    var $errors = false;

    function __construct()
    {
        $arModuleVersion = array();

        include(__DIR__.'/version.php');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("AWZ_FLASHCALLAPI_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("AWZ_FLASHCALLAPI_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("AWZ_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("AWZ_PARTNER_URI");
    }

    function InstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;
        $filePath = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/". $this->MODULE_ID ."/install/db/".mb_strtolower($DB->type)."/install.sql";
        if(!file_exists($filePath)) return true;
        $this->errors = $DB->RunSQLBatch($filePath);
        if (!$this->errors) {
            return true;
        } else {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return $this->errors;
        }
        return true;
    }


    function UnInstallDB()
    {
        global $DB, $DBType, $APPLICATION;

        $this->errors = false;
        $filePath = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/". $this->MODULE_ID ."/install/db/".mb_strtolower($DB->type)."/uninstall.sql";
        if(!file_exists($filePath)) return true;
        $this->errors = $DB->RunSQLBatch($filePath);
        if (!$this->errors) {
            return true;
        }
        else {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return $this->errors;
        }
    }


    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles()
    {
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION, $step;

        $this->InstallFiles();
        $this->InstallDB();
		$this->checkOldInstallTables();
        $this->InstallEvents();
        $this->createAgents();

        ModuleManager::RegisterModule($this->MODULE_ID);

        return true;
    }

    function DoUninstall()
    {
        global $APPLICATION, $step;

        $this->UnInstallDB();

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->deleteAgents();

        ModuleManager::UnRegisterModule($this->MODULE_ID);

        return true;
    }

    function createAgents() {
        return true;
    }

    function deleteAgents() {
        return true;
    }

	function checkOldInstallTables(){
		return true;
	}
}