# kami

Вызов компонента:

```php
<?$APPLICATION->IncludeComponent(
	"kami:catalog.news.list", 
	".default", 
	array(
		"CATALOG_IBLOCK_ID" => "2",
		"NEWS_IBLOCK_ID" => "1",
		"PROPERTY_NEWS_CODE" => "UF_NEWS_LINK",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?>
```

Тестирование https://develop.jubiks.pro/kami/.
На страницу дополнительно вывел содержимое массива `$arResult` с данными которые приходят в шаблон компонента
