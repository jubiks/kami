<?
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

Loc::loadMessages(__FILE__);

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"CATALOG_IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("CATALOG_IBLOCK_ID"),
            "TYPE" => "STRING",
		),
		"NEWS_IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("NEWS_IBLOCK_ID"),
            "TYPE" => "STRING",
		),
		"PROPERTY_NEWS_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("PROPERTY_NEWS_CODE"),
			"TYPE" => "STRING",
		),
		"CACHE_TIME"  =>  array(
		    "DEFAULT" => 36000000
        )
	),
);