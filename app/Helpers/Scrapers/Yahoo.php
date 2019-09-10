<?php

namespace App\Helpers\Scrapers;

use Exception;
use Carbon\Carbon;

class Yahoo implements IScraper
{
    private const YAHOO_DEF = [
        'exdd' => [
            'htmlAnchor' => 'data-test="EX_DIVIDEND_DATE-value"',
            'regex' => "@[0-9]{4}([-][0-9]{1,2}){2}@",
        ],
        'earnings' => [
            'htmlAnchor' => 'data-test="EARNINGS_DATE-value"',
            'regex' => "@[a-zA-Z]{3}\s[0-9]{1,2}[,]\s[0-9]{4}@",
        ],
        'pe' => [
            'htmlAnchor' => '" data-reactid="93">',
            'regex' => "@^[0-9]{1,}[.][0-9]{1,}@",
        ],
        'beta' => [
            'htmlAnchor' => '" data-reactid="88">',
            'regex' => "@^[-]{0,1}[0-9]{1,}[.][0-9]{1,}@",
        ],
        'mktcap' => [
            'htmlAnchor' => '" data-reactid="83">',
/*
 * dlaczego przy scrapowaniu MA nie dzieli tablicy po "MARKET_CAP-value"> ??
 */
//            'htmlAnchor' => '"MARKET_CAP-value">', //data-test="MARKET_CAP-value"><span class="Trsdu(0.3s) ">
            'regex' => "@^[0-9]{1,}[.][0-9]{1,}[A-Z]@",
        ],
        'price' => [
            'htmlAnchor' => 'class="Trsdu(0.3s) Fw(b) Fz(36px) Mb(-4px) D(ib)" data-reactid="34">',
            'regex' => "@^[0-9]*[.][0-9]*@",
        ]
    ];

    private const URL = 'https://finance.yahoo.com/quote/';

    private $ticker = null;
    private $maxAttempts = null;
    private $attempt = 1;
    private $messages = "";

    public function __construct(string $ticker, $maxAttempts = 5)
    {
        $this->ticker = $ticker;
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * @throws Exception
     */
    public function getData() : array
    {
        $data = [];

//        foreach ($this->ticker as $ticker) {

            print_r('Scraping ' . $this->ticker . PHP_EOL);

            $start_pre_page_load = microtime(true);

        $fileContents = $this->downloadPage();

//            $page = self::URL . $ticker;
//            try {
//                $fileContents = $this->downloadPage($this->ticker); //file_get_contents($page);
//            } catch (ErrorException $e) {
//                sleep(25);
//                set_time_limit(30);
//                $fileContents = $this->downloadPage($this->ticker); //file_get_contents($page);
//                print_r("Page could not be loaded for $this->ticker");
//            }

            $start_post_page_load = microtime(true);
        $data = $this->extractData($fileContents);
        $price = $this->getPrice($fileContents);

//            try {
//                $data = $this->extractData($fileContents);
//            } catch (Exception $e) {
////                sleep(25);
////                set_time_limit(30);
////                $data = $this->extractData($fileContents);
//                $this->retry("Scrapp was stopped on getData method for $this->ticker");
//            }

            print_r('Whole time ' . $time_elapsed_secs_pre = microtime(true) - $start_pre_page_load . PHP_EOL);
            print_r('Scrapping time ' . $time_elapsed_secs_post = microtime(true) - $start_post_page_load . PHP_EOL);

        return $this->formatData($data);
    }

    private function extractData($pageContents)
    {
        $rows = $this->getTableRows($this->getTableFromSite($pageContents));
        return $this->describeDataInRows($rows);
    }

    private function convertToFloat($row) : float
    {
        /*
         * yahoo returns cap as number with or without letter at the end, m for mil, b for bil and t for trillion
         * I want to get cap in millions
         */
        $code = ['M' => 1, 'B' => 1000, 'T' => 1000000];
        /*
         * if number ends with M, B or T use specified multiplier, else multiplier = 0 and number is full length of the string
         */
        $multiplier = in_array(substr($row, - 1), array_keys($code)) ? $code[substr($row, - 1)] : 0;

        return ($multiplier > 0) ? substr($row, 0, strlen($row) - 1) * $multiplier : $row;
    }

    private function downloadPage() : string
    {
        $page = self::URL . $this->ticker;
        $fileContents = file_get_contents($page);

        if ($fileContents  === false) {
            $this->retry("download page failed");
        }

        return $fileContents;
    }

    private function getTableFromSite(string $pageContent) : string
    {
        /*
         * summary is made on two tables
         */

        $table1 = explode('<table', $pageContent);

        if (count($table1) < 3) {
            $this->retry('It was not possible to extract two tables');
        }

        $table1 = explode('</table', $table1[1])[0];
        $table2 = explode('</table', explode('<table', $pageContent)[2])[0];

        return $table1 . $table2;
    }

    private function getTableRows(string $table) : array
    {
        return $rows = explode('<td', $table);
    }

    private function describeDataInRows(array $rows) : array
    {
        /*
         * first row has tbody styles and classes
         */
        unset($rows[0]);
        $index = 0;
        $output = [];
        $key = null;
        $value = null;
        foreach ($rows as &$row) {
            /*
             * data I want is just before first </ and after last >
             */
            $row = explode('</', $row)[0];
            $row = explode('>', $row);
            $row = $row[count($row) - 1];

            if ($index % 2 == 0) {
                $key = $this->cleanData($row);
            } else {
                $value = $this->cleanData($row);
                $output[$key] = $value;
            }
            $index++;
        }
        return $output;
    }

    private function cleanData(string $row)
    {
        /*
         * replace code for ' with ', code for & with /,
         * remove brackets
         * reformat number, remove '000 separator
         */
        $search = ['&#x27;', '&amp;', '(', ')', ',', 'N/A'];
        $replace = ["'", "/", "", "", "", ""];
        $row = str_replace($search, $replace, $row);
        /*
         * if empty return null
         */
        return empty($row) ? null : $row;
    }

    private function formatData(array $input) : array
    {

        $input['Market Cap'] = $this->convertToFloat($input['Market Cap']);
        $input['Ex-Dividend Date'] = Carbon::create($input['Ex-Dividend Date']);

        $input['Earnings Date 1'] = null;
        $input['Earnings Date 2'] = null;

        $dividend = explode(' ', $input['Forward Dividend / Yield']);
        $input['Forward Dividend'] = empty($dividend[0]) ? null : $dividend[0];
        $input['Yield'] = isset($dividend[1]) ? $dividend[1] : null;

        $input = array_merge($input, $this->splitEntry($input, '52 Week Range'));
        $input = array_merge($input, $this->splitEntry($input, "Day's Range"));
        $input = array_merge($input, $this->splitEntry($input, 'Earnings Date'));
        $input['Earnings Date 1'] = empty($input['Earnings Date 1']) ? null : Carbon::create($input['Earnings Date 1']);
        $input['Earnings Date 2'] = empty($input['Earnings Date 2']) ? null : Carbon::create($input['Earnings Date 2']);

        unset($input['Earnings Date']);

        return $input;
    }

    private function getPrice(string $pageContents) : array
    {

        $htmlAnchor = 'class="Trsdu(0.3s) Fw(b) Fz(36px) Mb(-4px) D(ib)" data-reactid="34">';
        $regex = "@^[0-9]*[.][0-9]*@";

        $data = explode($htmlAnchor, $pageContents)[1];
        $data = explode()[0];
        dd($data);

    }

    private function splitEntry($array, $indexName, $delimiter = '-')
    {
        $output= [];
        if (strtoupper($array[$indexName]) !== 'N/A' && !empty($array[$indexName])) {
            $temp = explode($delimiter, $array[$indexName]);
            $output[$indexName . " 1"] = rtrim(ltrim($temp[0]));
            $output[$indexName . " 2"] = null;

            if (count($temp) > 1) {
                $output[$indexName . " 2"] = rtrim(ltrim($temp[1]));
            }
        }

        return $output;
    }

    private function retry($message = "no message")
    {
        $this->attempt++;
        $this->messages .= "$this->attempt . $message ";
        if ($this->attempt > $this->maxAttempts) {
            throw new Exception("Failed to run Yahoo scrapper on $this->ticker " . "additional message: $this->messages");
        } else {
            sleep(25);
            set_time_limit(30);
            $this->getData();
        }
    }

}
