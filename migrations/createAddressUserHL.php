<?
define('BX_SESSION_ID_CHANGE', false);
define('NO_AGENT_CHECK', true);
define("STATISTIC_SKIP_ACTIVITY_CHECK", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER;
if (!$USER->isAdmin()) die('Нет доступа');

CModule::IncludeModule ('highloadblock');

/**
 * Создание HL
 */
echo "Создание HL<br>";
$idHL = 0;
$hlBlockID = \Bitrix\Highloadblock\HighloadBlockTable::getList(
    ['filter' => [
        'TABLE_NAME' => "user_address"
    ]]
)->fetch();
if(isset($hlBlockID['ID'])) {
    $idHL = intval($hlBlockID['ID']);
    echo "HL ID: " . $idHL . "<br>";
} else {
    $result = \Bitrix\Highloadblock\HighloadBlockTable::add([
        "NAME" => "UserAddress",
        "TABLE_NAME" => "user_address"
    ]);
    if(!$result->isSuccess()) {
        $errors = $result->getErrorMessages();
        echo "Ошибка: " . $errors . "<br>";
    } else {
        $idHL = intval($result->getId());
        echo "HL создан - ID: " . $idHL . "<br>";
    }
}

if($idHL > 0) {

    $arFields = [
        'FIELD_NAME' => 'UF_USER_ID',
        'USER_TYPE_ID' => 'integer',
        'NAME_RU' => 'USER_ID',
        'NAME_EN' => 'USER_ID',
        'SORT' => '10'
    ];
    $id = createHlProperty($idHL, $arFields);

    $arFields = [
        'FIELD_NAME' => 'UF_ADDRESS',
        'USER_TYPE_ID' => 'string',
        'NAME_RU' => 'Address',
        'NAME_EN' => 'Address',
        'SORT' => '20'
    ];
    $id = createHlProperty($idHL, $arFields);

    $arFields = [
        'FIELD_NAME' => 'UF_ACTIVE',
        'USER_TYPE_ID' => 'boolean',
        'NAME_RU' => 'Active',
        'NAME_EN' => 'Active',
        'SORT' => '40'
    ];
    $id = createHlProperty($idHL, $arFields);
}


function createHlProperty($highBlockID, $arFields) {
    //Создание свойств hl блока
    $arFields['ENTITY_ID'] = 'HLBLOCK_' . $highBlockID;
    $arFields['FIELD_NAME'] = (string)$arFields['FIELD_NAME'];

    if ($highBlockID <= 0)
        return false;

    if (empty ($arFields['FIELD_NAME']))
        return false;

    /* Подпись в форме редактирования */
    $arFields['EDIT_FORM_LABEL'] = array(
        'ru' => $arFields['NAME_RU'],
        'en' => $arFields['NAME_EN'],
    );

    /* Заголовок в списке */
    $arFields['LIST_COLUMN_LABEL'] = array(
        'ru' => $arFields['NAME_RU'],
        'en' => $arFields['NAME_EN'],
    );

    /* Подпись фильтра в списке */
    $arFields['LIST_FILTER_LABEL'] = array(
        'ru' => $arFields['NAME_RU'],
        'en' => $arFields['NAME_EN'],
    );

    /* Сообщение об ошибке (не обязательное) */
    $arFields['ERROR_MESSAGE'] = array(
        'ru' => 'Ошибка при заполнении пользовательского свойства',
        'en' => 'An error in completing the user field',
    );

    $highBlockPropID = 0;
    $obUserField = new \CUserTypeEntity();


    $res = $obUserField->GetList(
        array(),
        array(
            'ENTITY_ID' => $arFields['ENTITY_ID'],
            'FIELD_NAME' => $arFields['FIELD_NAME'],
        )
    );


    if ($ar = $res->Fetch()) {
        $highBlockPropID = $ar['ID'];
        $obUserField->Update($highBlockPropID, $arFields);
        return $highBlockPropID;
    } else {
        $highBlockPropID = $obUserField->Add($arFields);
        return $highBlockPropID;
    }
}
