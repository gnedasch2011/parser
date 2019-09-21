<?php


require('vendor/phpQuery/phpQuery/phpQuery.php');
require_once( 'D:\OSUltimate\SecondOSPanel\domains\parser\index.php');



function getURl($url)
{
    return file_get_contents($url);
}

function getPHPQuery($url)
{
    $html = getURl($url);

    return phpQuery::newDocument($html);
}


function findMainPageUrls($doc)
{
    $urls = $doc->find('.organic__url');

    foreach ($urls as $url) {
        $url = pq($url);
        $arr[] = $url->attr('href');
    }
    return $arr;
}

function clearUrlsOffYandex($arr)
{
    $newArr = [];
    if(is_array($arr)){
        foreach ($arr as $url){

            if(strpos($url, 'yabs.yandex')){
               continue;
            }
            $newArr[] = $url;

        }
    }

    return $newArr;
}

$url = 'https://habr.com/ru/post/69149/';
$file = 'C:\Users\2000\Desktop\yandex\массажные кресла — Яндекс_ нашлось 12 млн результатов.html';
$doc = getPHPQuery($file);


$urls = findMainPageUrls($doc);
$urlsClear = clearUrlsOffYandex($urls);

$fileInsite = 'C:\Users\2000\Desktop\Выдача\Массажные кресла, цены _ Купить в Москве с доставкой.html';

$doc = getPHPQuery($fileInsite);


$arrP = $doc->find('p');

foreach ($arrP as $p ) {

}


$dir = 'D:\OSUltimate\SecondOSPanel\domains\parser\vendor\phpmorphy-0.3.7\dicts';

// Укажите, для какого языка будем использовать словарь.
// Язык указывается как ISO3166 код страны и ISO639 код языка,
// разделенные символом подчеркивания (ru_RU, uk_UA, en_EN, de_DE и т.п.)

$lang = 'ru_RU';

// Укажите опции
// Список поддерживаемых опций см. ниже
$opts = array(
    'storage' => PHPMORPHY_STORAGE_FILE,
);

// создаем экземпляр класса phpMorphy
// обратите внимание: все функции phpMorphy являются throwable т.е.
// могут возбуждать исключения типа phpMorphy_Exception (конструктор тоже)
try {
    $morphy = new phpMorphy($dir, $lang, $opts);
} catch(phpMorphy_Exception $e) {
    die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
}

