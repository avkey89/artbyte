<?php

declare(strict_types=1);

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
    Bitrix\Main\Loader,
    Bitrix\Highloadblock\HighloadBlockTable as HL,
    Bitrix\Main\Localization\Loc as Loc;


class UserAddressList extends CBitrixComponent
{
    /**
     * кешируемые ключи arResult
     * @var array()
     */
    protected $cacheKeys = ['LIST'];

    /**
     * дополнительные параметры, от которых должен зависеть кеш
     * @var array
     */
    protected $cacheAddon = false;

    /**
     * парамтеры постраничной навигации
     * @var array
     */
    protected $navParams = [];

    /**
     * возвращаемые значения
     * @var mixed
     */
    protected $returned;

    /**
     * тегированный кеш
     * @var mixed
     */
    protected $tagCache;

    /**
     * подключает языковые файлы
     */
    public function onIncludeComponentLang()
    {
        $this->includeComponentLang(basename(__FILE__));
        Loc::loadMessages(__FILE__);
    }

    /**
     * Обработка входных параметров компоненты
     * @param $params
     */
    public function onPrepareComponentParams($params)
    {
        global $USER;
        $params["USER_ID"] = $USER->isAuthorized() ? $USER->GetID(): 0;
        $params['ACTIVE'] = $params['ACTIVE']==='Y';

        return $params;
    }

    /**
     * Проверка подключения модулей
     */
    protected function checkModules()
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * определяет читать данные из кеша или нет
     * @return bool
     */
    protected function readDataFromCache()
    {
        if ($this->arParams['CACHE_TYPE'] == 'N') {
            return false;
        }

        return !(
            $this->startResultCache(
                $this->arParams['CACHE_TIME'],
                $this->cacheAddon,
                "avkey.web/user.address.list/".md5(serialize($this->arParams["USER_ID"]))
            )
        );
    }

    /**
     * кеширует ключи массива arResult
     */
    protected function putDataToCache()
    {
        if (is_array($this->cacheKeys) && sizeof($this->cacheKeys) > 0) {
            $this->SetResultCacheKeys($this->cacheKeys);
        }
    }

    /**
     * прерывает кеширование
     */
    protected function abortDataCache()
    {
        $this->AbortResultCache();
    }

    protected function getResult()
    {
        $arResult = ["LIST"=>[]];

        if ($this->arParams["USER_ID"] > 0) {
            $hlBlockID = HL::getList(
                array('filter' => [
                    'TABLE_NAME' => "user_address"
                ])
            )->fetch();
            if (!empty($hlBlockID["ID"])) {
                $idHL = (int)$hlBlockID["ID"];
                $hlblock = HL::getById($idHL)->fetch();
                $entity = HL::compileEntity($hlblock);
                $entity_data_class = $entity->getDataClass();

                $filter = ["UF_USER_ID" => $this->arParams["USER_ID"]];
                if ($this->arParams["ACTIVE"]) {
                    $filter["UF_ACTIVE"] = 1;
                }

                $rsData = $entity_data_class::getList([
                    'select' => ["*"],
                    'filter' => $filter
                ]);

                while ($el = $rsData->fetch()) {
                    $arResult["LIST"][]["data"] = $el;
                }
            }
        }

        $this->arResult = $arResult;
    }

    public function executeComponent()
    {
        try {
            $this->checkModules();

            if(!$this->readDataFromCache()) {
                $this->getResult();
                $this->putDataToCache();
            }

            $this->includeComponentTemplate();

            $this->returned["arResult"] = $this->arResult;

            return $this->returned;
        } catch (Exception $e) {
            $this->abortDataCache();
            ShowError($e->getMessage());
        }
    }
}
