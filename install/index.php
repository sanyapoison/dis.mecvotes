<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class dis_mecvotes extends CModule
{

    const MODULE_ID = 'dis.mecvotes';
    var $MODULE_ID = self::MODULE_ID;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $strError = '';
    public $arExclusionAdminFiles;

    public function __construct()
    {
        $this->arExclusionAdminFiles = [
            '..',
            '.',
            'menu.php',
        ];

        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('DIS_MECVOTES.MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('DIS_MECVOTES.MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('DIS_MECVOTES.PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('DIS_MECVOTES.PARTNER_URI');

        $this->MODULE_GROUP_RIGHTS = 'N';
    }

    public function getPath($bNotDocumentRoot = false)
    {
        if ($bNotDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', str_replace('\\', '/', dirname(__DIR__)));
        } else {
            return dirname(__DIR__);
        }
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
        $this->InstallFiles();
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installDB()
    {
        $sPath = $this->getPath() . "/install/mysql/install/";
        $oConn = Application::getConnection();
        $arFiles = scandir($sPath);

        foreach ($arFiles as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $sQuery = file_get_contents($sPath . $file);
            $oConn->executeSqlBatch($sQuery);
        }
    }

    public function uninstallDB()
    {
        $sPath = $this->getPath() . "/install/mysql/uninstall/";
        $oConn = Application::getConnection();
        $arFiles = scandir($sPath);

        foreach ($arFiles as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $sQuery = file_get_contents($sPath . $file);
            $oConn->executeSqlBatch($sQuery);
        }
    }

    public function InstallFiles()
    {
        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->getPath() . '/admin')) {
            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->arExclusionAdminFiles)) {
                        continue;
                    }
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item,
                        '<' . '? require($_SERVER["DOCUMENT_ROOT"]."' . $this->getPath(true) . '/admin/' . $item . '");?' . '>');
                }
                closedir($dir);
            }
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->getPath() . '/assets')) {
            CopyDirFiles($path,
                $_SERVER["DOCUMENT_ROOT"] . "/local/themes/" . $this->MODULE_ID,
                true, true);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->getPath() . '/install/components/')) {
            CopyDirFiles($path,
                $_SERVER["DOCUMENT_ROOT"] . "/local/components/",
                true, true);
        }

        return true;
    }

    public function UnInstallFiles()
    {
        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->getPath() . '/admin')) {
            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . $this->getPath() . '/admin/',
                $_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin');
            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->arExclusionAdminFiles)) {
                        continue;
                    }
                    \Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
                }
                closedir($dir);
            }
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->getPath() . '/assets')) {
            DeleteDirFiles($path,
                $_SERVER["DOCUMENT_ROOT"] . '/local/themes/' . $this->MODULE_ID);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->getPath() . '/install/components/')) {
            $arComponents = scandir($path . '/ylab/');
            foreach ($arComponents as $component) {
                if ($component == '.' || $component == '..') {
                    continue;
                }
                DeleteDirFilesEx("/bitrix/components/ylab/" . $component . "/");
            }
        }

        return true;
    }
}
