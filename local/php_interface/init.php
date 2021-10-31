<?
use Bitrix\Main\IO;
use Bitrix\Main\Application;

$eventManager->addEventHandler("", "UserAddressOnAfterUpdate", "hlUserAddressUpdate");
$eventManager->addEventHandler("", "UserAddressOnAfterAdd", "hlUserAddressUpdate");
function hlUserAddressUpdate(Entity\Event $event)
{
    $arFields = $event->getParameter('fields');
    if(!empty($arFields["UF_USER_ID"])) {
        $userID = $arFields["UF_USER_ID"];
        $cache_path = "/bitrix/cache/avkey.web/user.address.list/".md5(serialize($userID));
        $cacheDir = new IO\Directory(Application::getDocumentRoot() . $cache_path);
        if($cacheDir->isExists()) {
            $cacheDir->delete();
        }
    }
}
