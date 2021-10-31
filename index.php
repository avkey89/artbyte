<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("User Address");

$APPLICATION->IncludeComponent(
    "avkey.web:user.address.list",
    "",
    [
        "CACHE_TYPE" => "Y",
        "CACHE_TIME" => "86400",
        "ACTIVE" => "Y"
    ],
    false
);

$APPLICATION->IncludeComponent(
    "avkey.web:user.address.list",
    "",
    [
        "CACHE_TYPE" => "Y",
        "CACHE_TIME" => "86400",
        "ACTIVE" => "N"
    ],
    false
);


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
