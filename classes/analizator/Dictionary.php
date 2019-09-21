<?php

namespace classes\analizator;

class Dictionary
{
    public $word;

    public function __construct($word)
    {
        require_once("vendor/phpmorphy-0.3.7/src/common.php");
        $dir = '/vendor/phpmorphy-0.3.7/dicts';
        $lang = 'ru_RU';
        $opts = array(
            'storage' => PHPMORPHY_STORAGE_FILE,
        );

        $morphy = new phpMorphy($dir, $lang, $opts);

        $this->morphy = $morphy;
        $this->word = $word;
    }

    public function getAllForms($word)
    {
        $result = $this->morphy->getAllForms(mb_strtoupper($word));
        return $result;
    }
}