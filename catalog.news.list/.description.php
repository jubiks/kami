<?
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	"NAME" => Loc::getMessage("CATALOG_NEWS_NAME"),
	"DESCRIPTION" => Loc::getMessage("CATALOG_NEWS_DESCRIPTION"),
	"ICON" => "/images/catalog_list.gif",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "catalog",
			"NAME" => Loc::getMessage("IBLOCK_DESC_CATALOG"),
			"SORT" => 10,
			"CHILD" => array(
				"ID" => "catalog_cmpx",
			),
		),
	),
);