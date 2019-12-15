<?php

namespace App\Helpers\Scrapers;

use App\Helpers\Scrapers\DTO\YahooScraperDTO;
use Exception;
use Carbon\Carbon;

class YahooScrapper implements IScraper
{
    private const URL = 'https://finance.yahoo.com/quote/';

    private $ticker = null;
    private $maxAttempts = null;
    private $attempt = 1;
    private $messages = [];
    private $fileContents = null;
    private $stockData = null;
    private $dto;

    public function __construct(string $ticker, $maxAttempts = 5)
    {
        $this->ticker = $ticker;
        $this->maxAttempts = $maxAttempts;
        $this->dto = new YahooScraperDTO();
    }

    /**
     * @throws Exception
     */
    public function getData() : array
    {
        $result = null;

        while ($this->attempt < $this->maxAttempts && ! $result) {
            $this->attempt++;
            try {
                $result = $this->_getData();
            } catch (Exception $e) {
                set_time_limit(30);
                $this->messages[$this->attempt] = $e->getMessage();
                sleep(25);
                set_time_limit(30);
            }
            finally {
                print_r($this->messages);
            }
        }
        return $result;
    }

    private function saveToDto()
    {
//        $this->dto->setEarningsSurprise($this->)
    }

    public function getMessages() : array
    {
        return $this->messages;
    }

    private function _getData()
    {
        print_r('Scraping ' . $this->ticker . PHP_EOL);

        $fileContents = $this->downloadPage();
        $earningsSurprise = $this->getEarningsSurprise();
//        $this->dto->setEarningsSurprise($earningsSurprise);

        $data = $this->extractData($fileContents);
        $price = $this->getPrice($fileContents);

        return $this->stockData = $this->formatData(array_merge($data, $price, $earningsSurprise));

//        return $this->formatData(array_merge($data, $price));
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
        $this->fileContents = file_get_contents($page);

        return $this->fileContents;
    }

    private function getTableFromSite(string $pageContent) : string
    {
        /*
         * quick fix - if earnings are set as range, they are presented as:
         * <span data-reactid="103">Oct 28, 2019</span><!-- react-text: 104 --> - <!-- /react-text --><span data-reactid="105">Nov 1, 2019</span>
         * so I want this </span><!-- react-text: 104 --> - <!-- /react-text --><span data-reactid="105"> to be replaced by this " - "
         */
        $pageContent = str_replace('</span><!-- react-text: 104 --> - <!-- /react-text --><span data-reactid="105">', "-", $pageContent);

        /*
         * summary is made on two tables
         */

        $table1 = explode('<table', $pageContent);

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

        $data = explode($htmlAnchor, $pageContents)[1];
        $data = explode('</', $data)[0];

        $output["Last price"] = $this->cleanData($data);
        return $output;
    }

    private function splitEntry($array, $indexName, $delimiter = '-')
    {
        $output[$indexName . " 1"] = null;
        $output[$indexName . " 2"] = null;
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

    /**
     * @return array
     */
    public function getStockData()
    {
        return $this->stockData;
    }

    public function getPageContents()
    {
        return $this->fileContents;
    }

    private function getEarningsSurprise() : array
    {
        $earningsSurpriseData = explode('earningsChart', $this->fileContents);
        if (! isset($earningsSurpriseData[2])) {
            return [];
        }
        $earningsSurpriseData = $earningsSurpriseData[2];
        $earningsSurpriseData = explode('financialsChart', $earningsSurpriseData)[0];
        $earningsSurpriseData = ltrim($earningsSurpriseData, '":');
        $earningsSurpriseData = rtrim($earningsSurpriseData, ',"');

        return ['earnings_surprise' => json_decode($earningsSurpriseData)];
    }

}
