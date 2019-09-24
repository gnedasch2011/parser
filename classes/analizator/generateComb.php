<?php
/**
 * Генератор комбинаций символов
 * @param $arr
 * @return array
 */
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

/**
 * Проверка, повторяется ли символ в слове
 * @param $word
 * @return bool
 */
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

function valueForBrut36($count)
{
//    $str = '';
//    $count = count($arrSet);
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
