<?php

namespace base\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class ConvertAmountToWordsPl extends AbstractPlugin
{
    private $words = Array(
        'minus',

        Array(
            'zero',
            'jeden',
            'dwa',
            'trzy',
            'cztery',
            'pięć',
            'sześć',
            'siedem',
            'osiem',
            'dziewięć'),

        Array(
            'dziesięć',
            'jedenaście',
            'dwanaście',
            'trzynaście',
            'czternaście',
            'piętnaście',
            'szesnaście',
            'siedemnaście',
            'osiemnaście',
            'dziewiętnaście'),

        Array(
            'dziesięć',
            'dwadzieścia',
            'trzydzieści',
            'czterdzieści',
            'pięćdziesiąt',
            'sześćdziesiąt',
            'siedemdziesiąt',
            'osiemdziesiąt',
            'dziewięćdziesiąt'),

        Array(
            'sto',
            'dwieście',
            'trzysta',
            'czterysta',
            'pięćset',
            'sześćset',
            'siedemset',
            'osiemset',
            'dziewięćset'),

        Array(
            'tysiąc',
            'tysiące',
            'tysięcy'),

        Array(
            'milion',
            'miliony',
            'milionów'),

        Array(
            'miliard',
            'miliardy',
            'miliardów'),

        Array(
            'bilion',
            'biliony',
            'bilionów'),

        Array(
            'biliard',
            'biliardy',
            'biliardów'),

        Array(
            'trylion',
            'tryliony',
            'trylionów'),

        Array(
            'tryliard',
            'tryliardy',
            'tryliardów'),

        Array(
            'kwadrylion',
            'kwadryliony',
            'kwadrylionów'),

        Array(
            'kwintylion',
            'kwintyliony',
            'kwintylionów'),

        Array(
            'sekstylion',
            'sekstyliony',
            'sekstylionów'),

        Array(
            'septylion',
            'septyliony',
            'septylionów'),

        Array(
            'oktylion',
            'oktyliony',
            'oktylionów'),

        Array(
            'nonylion',
            'nonyliony',
            'nonylionów'),

        Array(
            'decylion',
            'decyliony',
            'decylionów')
    );

    private $plnCurrency = Array('złoty', 'złote', 'złotych');
    private $plnSubCurrency = Array('grosz', 'grosze', 'groszy');
    private $usdCurrency = Array('dollar', 'dollary', 'dollarów');
    private $usdSubCurrency = Array('cent', 'centy', 'centów');
    private $eurCurrency = Array('euro', 'euro', 'euro');
    private $eurSubCurrency = Array('cent', 'centy', 'centów');

    private function variant($variants, $int){
        $txt = $variants[2];
        if ($int == 1) $txt = $variants[0];
        $unites = (int) substr($int,-1);
        $rest = $int % 100;
        if (($unites > 1 && $unites < 5) &! ($rest > 10 && $rest < 20))
            $txt = $variants[1];

        return $txt;
    }

    private function number($int){
        $result = '';
        $j = abs((int) $int);

        if ($j == 0) return $this->words[1][0];
        $unities = $j % 10;
        $dozens = ($j % 100 - $unities) / 10;
        $hundreds = ($j - $dozens*10 - $unities) / 100;

        if ($hundreds > 0) $result .= $this->words[4][$hundreds-1].' ';

        if ($dozens > 0)
            if ($dozens == 1) $result .= $this->words[2][$unities].' ';
            else
                $result .= $this->words[3][$dozens-1].' ';

        if ($unities > 0 && $dozens != 1) $result .= $this->words[1][$unities].' ';

        return $result;
    }

    private function toWords($int){
        $in = preg_replace('/[^-\d]+/','',$int);
        $out = '';

        if ($in{0} == '-'){
            $in = substr($in, 1);
            $out = $this->words[0].' ';
        }

        $txt = str_split(strrev($in), 3);

        if ($in == 0) $out = $this->words[1][0].' ';

        for ($i = count($txt) - 1; $i >= 0; $i--){
            $number = (int) strrev($txt[$i]);
            if ($number > 0)
                if ($i == 0)
                    $out .= $this->number($number).' ';
                else
                    $out .= ($number > 1 ? $this->number($number).' ' : '')
                        .$this->variant( $this->words[4 + $i], $number).' ';
        }

        return trim($out);
    }

    public function __invoke($amount = null, $currency = null)
    {
        switch($currency) {
            case 'PLN';
                $currencyArr = $this->plnCurrency;
                $subCurrencyArr = $this->plnSubCurrency;
                break;
            case 'USD';
                $currencyArr = $this->usdCurrency;
                $subCurrencyArr = $this->usdSubCurrency;
                break;
            case 'EUR';
                $currencyArr = $this->eurCurrency;
                $subCurrencyArr = $this->eurSubCurrency;
                break;
            default;
                $currencyArr = $this->plnCurrency;
                $subCurrencyArr = $this->plnSubCurrency;
                break;
        }

        $amount = explode('.', $amount);

        $zl = preg_replace('/[^-\d]+/','', $amount[0]);
        $gr = preg_replace('/[^\d]+/','', substr(isset($amount[1]) ? $amount[1] : 0, 0, 2));

        while(strlen($gr) < 2) {
            $gr .= '0';
        }

        $result = $this->toWords($zl) . ' ' . $this->variant($currencyArr, $zl) .
            (intval($gr) == 0 ? '' : ' ' . $this->toWords($gr) . ' ' . $this->variant($subCurrencyArr, $gr));

        return $result;
    }
}