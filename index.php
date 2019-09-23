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

/**
 * Почистить, которых нет на странице
 * @param $querying
 * @return array
 */
function clearEmptyWordInPage($getALLFormWords, $arrLinkText)
{
    $clearEmptyWordInPage = [];

    if (is_array($getALLFormWords)) {
        foreach ($getALLFormWords as $query) {
            $count = countLinkInDocument($query, $arrLinkText);
            if ($count > 0) {
                $clearEmptyWordInPage[] = $query;
            }
        }
    } else {
        echo 'Execution';
    }

    return $clearEmptyWordInPage;
}

function getArrQueryingGroup($querying, $allLinkInPage)
{
    $arrQuerying = explode(' ', $querying);

    $arrQueryingGroup = [];
    foreach ($arrQuerying as $query) {
        $getALLFormWords = getALLFormWords($query);
        $clearEmptyWordInPage = clearEmptyWordInPage($getALLFormWords, $allLinkInPage);
        if (!empty($clearEmptyWordInPage)) {
            $arrQueryingGroup[$query] = $clearEmptyWordInPage;
        }
    }
    return $arrQueryingGroup;
}


function countLinkInDocument($query, $arrLinkText)
{
    $count = 0;
    foreach ($arrLinkText as $textLink => $link) {
        $textLink = mb_strtolower($textLink);
        $query = trim(mb_strtolower($query));

        preg_match("/(" . $query . ") /", $textLink, $matches);
        if (!empty($matches)) {
            $count++;
        }
    }
    return $count;
}

function allLinkInPage($document)
{
    $arrLink = $document->find('a');
    $arrLinkText = [];

    foreach ($arrLink as $link) {
        $link = pq($link);
        $arrLinkText[trim($link->html())] = trim($link->attr('href'));
    }

    return $arrLinkText;
}


function getArrQueryCount($arrQueryingGroup, $allLinkInPage)
{
    $arrQueryCount = [];

    foreach ($arrQueryingGroup as $group) {
        foreach ($group as $query) {
            $count = countLinkInDocument($query, $allLinkInPage);
            if ($count > 0) {
                $arrQueryCount[$query] = $count;
            }

        }
    }

    return $arrQueryCount;

}

function getAllCombinations($arrays)
{
    $result = array(array());
    foreach ($arrays as $property => $property_values) {
        $tmp = array();
        foreach ($result as $result_item) {
            foreach ($property_values as $property_value) {
                $tmp[] = array_merge($result_item, array($property => $property_value));
            }
        }
        $result = $tmp;
    }
    return $result;
}

function brut36($arr)
{
    $A = $arr['variants'] ?? "0123456789";
    $N = $arr['number'] ?? 1;

    $base = "0123456789abcdefghijklmnopqrstuvwxyz";
    $b = strlen($A);
    $count = pow($b, $N);
    $res = [];

    $countVariantSymbol = mb_strlen($A);

    for ($j = 1; $j < $countVariantSymbol; $j++) {
        $A = trim(substr($A, 0, -1));

        for ($i = 0; $i < $count; $i++) {
            $res[] = strtr(str_pad(base_convert($i, 10, $b), $N, "0",
                STR_PAD_LEFT), $base, $A);
        }

        $N--;

    }
    $res = clearRerArr($res);

    return $res;
}

function checkRepeatSymbol($word)
{
    $count_chars = count_chars($word, 1);     //  [48] => 4
    foreach ($count_chars as $char) {
        if ($char > 1) {
            return false;
        }
    }

    return true;
}

function clearRerArr($res)
{
    $arrResult = [];

    foreach ($res as $val) { //$val 0000
        if (checkRepeatSymbol($val)) {
            $arrResult[] = $val;
        }
        continue;
    }

    return $arrResult;
}

function valueForBrut36($arrSet)
{
    $str = '';
    $count = count($arrSet);
    for ($i = 0; $i < $count; $i++) {
        $str .= $i;
    }

    $arr['variants'] = $str;
    $arr['number'] = $count;

    return $arr;
}


function allVariantSetKeys($set)
{
    return brut36(valueForBrut36($set));
}


function generateSentence($set, $allVariantSetKeys)
{
    $res = [];
    foreach ($allVariantSetKeys as $setKeys) {

        $string = '';
        $strSplit = str_split($setKeys);
        $values = array_values($set);

        for ($i = 0; $i < count($strSplit); $i++) {
            $string .= $values[$strSplit[$i]] . ' ';

        }

        $res[] = $string;


    }
    return $res;
}

function mergeArray($resAllSet)
{
    $commonArr = [];
    for ($i = 0; $i < count($resAllSet); $i++) {
        foreach ($resAllSet[$i] as $query) {
            $commonArr [] = $query;
        }
    }

    return $commonArr;
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

$fileInsite = 'C:\Users\2000\Downloads\Массажные кресла, цены _ Купить в Москве с доставкой.html';
$document = getPHPQuery($fileInsite);
$allLinkInPage = allLinkInPage($document);
$arrQueryingGroup = getArrQueryingGroup('Массажные купить кресла', $allLinkInPage);
//оставить только те которые встречаются
//сколько запрос-повторение
$arrQueryCount = getArrQueryCount($arrQueryingGroup, $allLinkInPage);
$keyForCombination = array_keys($arrQueryCount);

$getAllCombinations = getAllCombinations($arrQueryingGroup);
//находим все словосочетания из слов, которые были на странице

foreach ($getAllCombinations as $set) {
    $allVariantSet = allVariantSetKeys($set);
    $resAllSet[] = generateSentence($set, $allVariantSet);
}

$commonArr = mergeArray($resAllSet);


foreach ($commonArr as $query) {
    echo $query . '--------' . countLinkInDocument($query, $allLinkInPage);echo '<br>';
}

