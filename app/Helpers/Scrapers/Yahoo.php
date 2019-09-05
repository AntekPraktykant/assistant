<?php

namespace App\Helpers\Scrapers;

use Carbon\Carbon;

class Yahoo implements IScraper
{
    private const YAHOO_DEF =
        [
            'exdd' =>
                [
                    'htmlAnchor' => 'data-test="EX_DIVIDEND_DATE-value"',
                    'regex' => "@[0-9]{4}([-][0-9]{1,2}){2}@",
                ],
            'earnings' =>
                [
                    'htmlAnchor' => 'data-test="EARNINGS_DATE-value"',
                    'regex' => "@[a-zA-Z]{3}\s[0-9]{1,2}[,]\s[0-9]{4}@",
                ],
        ];
    private const URL = 'https://finance.yahoo.com/quote/';

    private $data = [];

    public function __construct(array $tickers)
    {
        foreach ($tickers as $ticker) {
            $page = self::URL . $ticker;
            $fileContents = file_get_contents($page);

            $this->data[$ticker]['earnings'] = $this->extractData('earnings', $fileContents);
            $this->data[$ticker]['exdd'] = $this->extractData('exdd', $fileContents);
        }
    }

    private function extractData($element, $pageContents)
    {
        if (! in_array($element, array_keys(self::YAHOO_DEF))) {
            die('You are trying to get data for an element that is not defined in YAHOO_DEF');
        }

        $array = explode(self::YAHOO_DEF[$element]['htmlAnchor'], $pageContents);
        $array = explode('</td>', $array[1]);

        $results = [];
        preg_match_all(self::YAHOO_DEF[$element]['regex'], $array[0], $results);

        /*
         * we want to get only exact matches so return only index 0
         */
        return $this->convertToCarbon($results[0]);
    }

    private function convertToCarbon(array $array)
    {
        foreach ($array as &$row) {
            if (!empty($row)) {
                $row = Carbon::create($row);
            }
        }

        return $array;
    }

    public function getData() : array
    {
        return $this->data;
    }
}