<?php
/**
 * Created by PhpStorm.
 * User: marcin
 * Date: 03/05/15
 * Time: 16:22
 */

namespace base\Library;

class ExchangeRate {

    private $contents = '';

    function __construct($url) {
        $this->contents = file_get_contents($url);
        if($this->contents == FALSE) {
            throw new \Exception('Unable to fetch exchange rates');
        }
    }

    private function convert($text) {
        $charset = '';

        if($charset && function_exists('iconv')) {
            return iconv('utf-8', $charset, $text);
        }
        elseif($charset && function_exists('recode_string')) {
            return recode_string('utf8...'.$charset, $text);
        }
        else
        {
            return $text;
        }
    }

    public function find($fields) {
        if(!is_array($fields)) {
            $fields = array($fields);
        }

        $last = libxml_use_internal_errors(TRUE);
        $info = new \SimpleXMLElement($this->contents);
        libxml_use_internal_errors($last);

        $rates = array(
            'tableNumber' => (string)$info->numer_tabeli,
            'publishDate' => (string)$info->data_publikacji
        );

        foreach($info->pozycja as $v) {
            $kod = (string)$v->kod_waluty;
            $rates[$kod] = array(
                'name' => $this->convert((string)$v->nazwa_waluty),
                'qty' => (string)$v->przelicznik
            );
            foreach($fields as $field) {
                $rates[$kod][$field] = (string)$v->$field;
            };
        }

        return $rates;
    }
}