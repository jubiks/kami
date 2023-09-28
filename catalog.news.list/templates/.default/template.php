<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
/** @var \CBitrixComponentTemplate $this */

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("ui.bootstrap4");

d($arResult);
?>

<div class="container">
    <h1 class="mb-5">Элементов - <?=$arResult['PRODUCT_ITEMS_COUNT']?></h1>
    <ul>
        <?foreach($arResult['NEWS_ITEMS'] as $arNews):?>
        <li>
            <strong><?=$arNews['NAME']?></strong> - <?=is_object($arNews['ACTIVE_FROM']) ? $arNews['ACTIVE_FROM']->format('d.m.Y') : ''?> (<?=join(', ', $arNews['SECTIONS_NAME'])?>)
            <ul>
                <?foreach($arResult['PRODUCT_ITEMS'] as $arProduct): if(!in_array($arProduct['SECTION_ID'],$arNews['SECTIONS_ID'])) continue;?>
                <li><?=$arProduct['NAME']?> - <?=$arProduct['PRICE_FORMAT']?> - <?=$arProduct['MATERIAL_VALUE']?> - <?=$arProduct['ARTNUMBER_VALUE']?></li>
                <?endforeach?>
            </ul>
        </li>
        <?endforeach?>
    </ul>
</div>