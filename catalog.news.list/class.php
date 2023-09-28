<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class KamiCatalogNewsList extends CBitrixComponent
{
	/** @var ErrorCollection $errors Errors. */
	protected $errors;

    protected function addError($message, $code = '')
    {
        $this->errors->setError(new \Bitrix\Main\Error($message, $code));
    }

    protected function getErrors()
    {
        $arErrors = [];
        foreach ($this->errors as $error)
        {
            $arErrors[] = $error->getMessage();
        }

        return $arErrors;
    }

    protected function printErrors()
    {
        foreach ($this->errors as $error)
        {
            ShowError($error);
        }
    }

	protected function checkRequiredParams()
	{
	    if(!\Bitrix\Main\Loader::includeModule('iblock')){
            $this->addError(Loc::getMessage('ERROR_IBLOCK_MODULE_NOT_INSTALLED'));
            return false;
        }

	    if(!\Bitrix\Main\Loader::includeModule('catalog')){
            $this->addError(Loc::getMessage('ERROR_CATALOG_MODULE_NOT_INSTALLED'));
            return false;
        }

	    if(!\Bitrix\Main\Loader::includeModule('currency')){
            $this->addError(Loc::getMessage('ERROR_CURRENCY_MODULE_NOT_INSTALLED'));
            return false;
        }

	    if(!$this->arParams['CATALOG_IBLOCK_ID']){
	        $this->addError(Loc::getMessage('ERROR_CATALOG_IBLOCK_ID_EMPTY'));
	        return false;
        }

	    if(!$this->arParams['NEWS_IBLOCK_ID']){
	        $this->addError(Loc::getMessage('ERROR_NEWS_IBLOCK_ID_EMPTY'));
	        return false;
        }

	    if(empty($this->arParams['PROPERTY_NEWS_CODE'])){
	        $this->addError(Loc::getMessage('ERROR_PROPERTY_NEWS_CODE_EMPTY'));
	        return false;
        }

        return true;
	}

	protected function initParams()
	{
        $this->arParams['CATALOG_IBLOCK_ID'] = intval($this->arParams['CATALOG_IBLOCK_ID']);
        $this->arParams['NEWS_IBLOCK_ID'] = intval($this->arParams['NEWS_IBLOCK_ID']);
        $this->arParams['PROPERTY_NEWS_CODE'] = !empty($this->arParams['PROPERTY_NEWS_CODE']) ? $this->arParams['PROPERTY_NEWS_CODE'] : '';
        $this->arParams['CACHE_TIME'] = !empty($this->arParams['CACHE_TIME']) ? intval($this->arParams['CACHE_TIME'] ): 3600000;
	}

	protected function prepareResult()
	{
		return true;
	}

    private function GetData()
    {
        $this->arResult['NEWS_ITEMS'] = [];
        $this->arResult['SECTION_ITEMS'] = [];
        $this->arResult['PRODUCT_ITEMS'] = [];
        $this->arResult['PRODUCT_ITEMS_COUNT'] = 0;

        $arNewsIds = [];
        $arSectionIds = [];

        $res = \Bitrix\Iblock\ElementTable::getList([
            'select' => ['ID', 'NAME', 'ACTIVE_FROM'],
            'filter' => ['IBLOCK_ID' => $this->arParams['NEWS_IBLOCK_ID'], '=ACTIVE' => 'Y'],
            'cache' => ['ttl' => $this->arParams['CACHE_TIME']],
            //'limit' => 1
        ]);

        while($arElement = $res->fetch()){
            $arNewsIds[] = $arElement['ID'];
            $this->arResult['NEWS_ITEMS'][] = $arElement;
        }

        if(!sizeof($arNewsIds)) return;

        $res = \Bitrix\Iblock\Model\Section::compileEntityByIblock($this->arParams['CATALOG_IBLOCK_ID'])::getList([
            'select' => ['ID', 'NAME', 'NEWS_ID' => $this->arParams['PROPERTY_NEWS_CODE']],
            'filter' => ['IBLOCK_ID' => $this->arParams['CATALOG_IBLOCK_ID'], $this->arParams['PROPERTY_NEWS_CODE'] => $arNewsIds, 'GLOBAL_ACTIVE' => 'Y', '=ACTIVE' => 'Y'],
            'cache' => ['ttl' => $this->arParams['CACHE_TIME']],
        ]);

        while($arSection = $res->fetch()){
            $arSectionIds[] = $arSection['ID'];
            $this->arResult['SECTION_ITEMS'][] = $arSection;
        }

        foreach($this->arResult['NEWS_ITEMS'] as $k => $v){
            foreach($this->arResult['SECTION_ITEMS'] as $vv){
                if(in_array($v['ID'],$vv['NEWS_ID'])){
                    $this->arResult['NEWS_ITEMS'][$k]['SECTIONS_NAME'][] = $vv['NAME'];
                    $this->arResult['NEWS_ITEMS'][$k]['SECTIONS_ID'][] = $vv['ID'];
                }
            }
        }

        if(!sizeof($arSectionIds)) return;

        $res = \Bitrix\Catalog\GroupTable::getList([
            'filter' => ['BASE' => 'Y'],
            'cache' => ['ttl' => $this->arParams['CACHE_TIME']],
        ]);

        if($arGroup = $res->fetch()) {
            $res = \Bitrix\Iblock\Iblock::wakeUp($this->arParams['CATALOG_IBLOCK_ID'])->getEntityDataClass()::getList([
                'count_total' => true,
                'select' => [
                    'ID',
                    'NAME',
                    'SECTION_ID' => 'IBLOCK_SECTION_ID',
                    'ARTNUMBER_VALUE' => 'ARTNUMBER.VALUE',
                    'MATERIAL_VALUE' => 'MATERIAL.VALUE',
                    'PRICE_VALUE' => 'PRICE.PRICE',
                    'PRICE_CARRENCY' => 'PRICE.CURRENCY'
                ],
                'filter' => [
                    'IBLOCK_ID' => $this->arParams['CATALOG_IBLOCK_ID'],
                    'IBLOCK_SECTION_ID' => $arSectionIds,
                    '=ACTIVE' => 'Y',
                    '=PRICE.CATALOG_GROUP_ID' => $arGroup['ID']
                ],
                'cache' => [
                    'ttl' => $this->arParams['CACHE_TIME']
                ],
                'runtime' => [
                    'PRICE' => [
                        'data_type' => '\Bitrix\Catalog\PriceTable',
                        'reference' => [
                            'ref.PRODUCT_ID' => 'this.ID'
                        ]
                    ]
                ]
            ]);

            while ($arProduct = $res->fetch()) {
                if($arProduct['PRICE_VALUE']) $arProduct['PRICE_FORMAT'] = CurrencyFormat($arProduct['PRICE_VALUE'],$arProduct['PRICE_CARRENCY']);
                $this->arResult['PRODUCT_ITEMS'][] = $arProduct;
            }

            $this->arResult['PRODUCT_ITEMS_COUNT'] = $res->getCount();
        }
    }

	public function executeComponent()
	{
	    global $APPLICATION;

        $this->errors = new ErrorCollection();
		$this->initParams();
        
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}
        
        $this->GetData();

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();

        $APPLICATION->SetTitle(Loc::getMessage('PAGE_TITLE', ['#COUNT#' => $this->arResult['PRODUCT_ITEMS_COUNT']]));
        $APPLICATION->SetPageProperty('title', Loc::getMessage('PAGE_TITLE', ['#COUNT#' => $this->arResult['PRODUCT_ITEMS_COUNT']]));

	}
}