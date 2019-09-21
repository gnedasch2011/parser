<?php

require_once('vendor/phpQuery/phpQuery/phpQuery.php');
require_once('vendor/phpmorphy-0.3.7/src/common.php');


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
    if (is_array($arr)) {
        foreach ($arr as $url) {

            if (strpos($url, 'yabs.yandex')) {
                continue;
            }
            $newArr[] = $url;

        }
    }

    return $newArr;
}

function getALLFormWords($word)
{
    try {
        $dir = 'vendor/phpmorphy-0.3.7/dicts';
        $lang = 'ru_RU';
        $opts = array(
            'storage' => PHPMORPHY_STORAGE_FILE,
        );

        $morphy = new phpMorphy($dir, $lang, $opts);
        $result = $morphy->getAllForms(mb_strtoupper($word));
        return $result;

    } catch (phpMorphy_Exception $e) {
        die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
    }

}

function getArrQueryingGroup($querying)
{
    $arrQuerying = explode(' ', $querying);

    $arrQueryingGroup = [];

    foreach ($arrQuerying as $query) {
        $arrQueryingGroup[0][$query] = getALLFormWords($query);
    }
    return $arrQueryingGroup;
}


function countLinkInDocument($query, $arrLinkText)
{
    $count = 0;

    foreach ($arrLinkText as $textLink => $link) {
        $textLink = mb_strtolower($textLink);
        $query = mb_strtolower($query);

        if (strpos($textLink, $query) !== false) {
            $count++;
        }
    }

    return $count;
}

/**
 * Поиск на главной страниц
 */
//$url = 'https://habr.com/ru/post/69149/';
//$file = 'C:\Users\2000\Desktop\yandex\массажные кресла — Яндекс_ нашлось 12 млн результатов.html';
//
//$doc = getPHPQuery($file);
//$urls = findMainPageUrls($doc);
//$urlsClear = clearUrlsOffYandex($urls);

$fileInsite = 'C:\Users\2000\Desktop\Выдача\Массажные кресла, цены _ Купить в Москве с доставкой.html';


$document = getPHPQuery($fileInsite);
$arrLink = $document->find('a');
$arrLinkText = [];

foreach ($arrLink as $link) {
    $link = pq($link);
    $arrLinkText[trim($link->text())] = trim($link->attr('href'));
}


$arrQueryingGroup = getArrQueryingGroup('Массажные кресла');

$arrQueryCount = [];

foreach ($arrQueryingGroup[0] as $group) {
    foreach ($group as $query) {
        $arrQueryCount[$query] = countLinkInDocument($query, $arrLinkText);
    }
}


echo "<pre>";
print_r($arrQueryCount);
die();


foreach ($arrP as $p) {
    $p = pq($p);
    echo $p->text();

}






// создаем экземпляр класса phpMorphy
// обратите внимание: все функции phpMorphy являются throwable т.е.
// могут возбуждать исключения типа phpMorphy_Exception (конструктор тоже)

