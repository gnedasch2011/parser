<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<?php

require_once('vendor/phpQuery/phpQuery/phpQuery.php');
require_once('vendor/phpmorphy-0.3.7/src/common.php');
global $comb;


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

/**
 * Получить массив запросов по группам
 * @param $querying
 * @param $allLinkInPage
 * @return array
 */
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

/**
 * Посчитать сколько встречается запрос в ссылках
 * @param $query
 * @param $arrLinkText
 * @return int
 */
function countLinkInDocument($query, $arrLinkText): int
{
    $count = 0;
    foreach ($arrLinkText as $textLink) {
        $textLink = mb_strtolower($textLink);
        $query = trim(mb_strtolower($query));

//        if($query=='купить'){
//            preg_match("/(" . $query . ")/", $textLink, $matches);
//           echo "<pre>"; print_r($textLink);die();
//            if (!empty($matches)) {
//                $count++;
//            }
//
//        }

        preg_match("/(" . $query . ") /", $textLink, $matches);
        if (!empty($matches)) {
            $count++;
        }
    }
    return $count;
}

/**
 * Ищет все анкоры на странице
 * @param $document
 * @return array
 */
function allLinkInPage($document): array
{
    $arrLink = $document->find('a');
    $arrLinkText = [];

    foreach ($arrLink as $link) {
        $link = pq($link);
        $arrLinkText[($link->htmlOuter())] = trim($link->attr('href'));
    }

    return $arrLinkText;
}


/**
 * Считает сколько совпадений на странице
 * @param $arrQueryingGroup
 * @param $allLinkInPage
 * @return array
 */
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

/**
 * Собирает все комбинации из массивов
 * @param $arrays
 * @return array
 */
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


/**
 * Генерация результатов из всех возможных комбинаций
 * @param $set
 * @param $allVariantSetKeys
 * @return array
 */
function generateSentence($set, $allVariantSetKeys)
{
    $res = [];
    foreach ($allVariantSetKeys as $setKeys) {
        $string = '';
        $strSplit = str_split($setKeys);
        $values = array_values($set);

        for ($i = 0; $i < count($strSplit); $i++) {
            $string .= trim($values[$strSplit[$i]]) . ' ';

        }
        $res[] = trim($string);
    }
    return array_unique($res);
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

function computeInArrays($array)
{
    $key = '';
    $v = 0;
    $res = [];

    foreach ($array as $k => $v) {
        $key .= $k . ', ';
        $v += $v;
    }
    $keyNew = substr($key, 0, -2);
    $res[$keyNew] = $v;
    return $res;
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
function allLinkInPageClear($allLinkInPage, $query)
{
    $newArrLink = [];

    $arrQuery = explode(' ', $query);
    foreach ($arrQuery as $q) {
        $query = trim(mb_strtolower($q));
        $countSymbol = mb_strlen($query);
        $rezQuery = mb_substr($query, 0, ceil($countSymbol * 0.6));

        foreach ($allLinkInPage as $link) {
            $link = trim(mb_strtolower($link));

            preg_match("/(" . $rezQuery . ")/", $link, $matches);

            if (!empty($matches)) {
                $newArrLink[] = $link;
            }
        }
        return $newArrLink;
    }

}

function commonMathes($page, $query): array
{

    $arrResult = [];
    $comb = require_once('combination/comb.php');
    $document = getPHPQuery($page);
    $allLinkInPage = allLinkInPage($document);

    $allLinkInPageClear = allLinkInPageClear(array_keys($allLinkInPage), $query);
    $arrQueryingGroup = getArrQueryingGroup($query, $allLinkInPageClear);
    /**
     * собирает комбинации из слов, которые есть на странице
     */
    $getAllCombinations = getAllCombinations($arrQueryingGroup);
    /**
     * прямое вхождение
     */

    $arrResult['directEntry'][$query] = countLinkInDocument($query, array_keys($allLinkInPage));

    /**
     * находим все словосочетания из слов, которые были на странице
     */
    $queryClone = $query;

    foreach ($getAllCombinations as $set) {
        $resAllSet[] = generateSentence($set, $comb[count($set)]);
    }
    $commonArr = mergeArray($resAllSet);

    $commonArrWithCount = [];
    foreach ($commonArr as $queryClone) {
        $count = countLinkInDocument($queryClone, array_keys($allLinkInPage));
        if ($count > 0) {
            $commonArrWithCount[$queryClone] = $count;
        }
    }

    $arrResult['commonArrWithCount'] = computeInArrays($commonArrWithCount);

    /**
     * находим все одиночные вхождения
     */

    $queryClone = $query;
    $querySingle = explode(' ', trim($queryClone));

    foreach ($querySingle as $queryClone) {
        $count = countLinkInDocument($queryClone, array_keys($allLinkInPage));
        if ($count > 0) {
            $commonArrSingleQuery[$queryClone] = $count;
        }
    }
    $arrResult['commonArrSingleQuery'] = $commonArrSingleQuery;

    /**
     * Количество словоформ одного слова
     */

    foreach ($querySingle as $query) {
        $queryAllForms = getALLFormWords($query);

        foreach ($queryAllForms as $wordForm) {
            $count = countLinkInDocument($wordForm, array_keys($allLinkInPage));
            if ($count > 0) {
                $queryAllFormsCounts[$query][$wordForm] = $count;
            }
        }

        foreach ($queryAllFormsCounts as $group => $arr) {
            $queryAllFormsCounts[$group] = computeInArrays($arr);
        }

    }

    $arrResult['queryAllForms'] = $queryAllFormsCounts;


    return $arrResult;
}

$fileInsite = 'C:\Users\2000\Desktop\analiz\Купить массажные кресла в Москве ★ цена, стоимость, доставка ★ «Массажные-Кресла.РФ».html';
$query = 'Массажные кресла купить';
$link2 = "C:\Users\Maks\Desktop\На отправку\Купить массажные кресла в Москве ⭐ цена, стоимость, доставка ⭐ «Массажные-Кресла.РФ».html";
//$link2 = 'https://xn----7sbabxbe8akco3bgai8m.xn--p1ai/categories/';
//$fileInsite = 'https://xn----7sbabxbe8akco3bgai8m.xn--p1ai/categories/';
echo '<pre>';
print_r(commonMathes($link2, $query));
?>
</body>
</html>
