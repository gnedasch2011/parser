<?php

namespace classes\analizator;

class Dictionary
{
    public $word;

    public function __construct($word)
    {

    }

    public function getAllForms($word)
    {
        $result = $this->morphy->getAllForms(mb_strtoupper($word));
        return $result;
    }
}