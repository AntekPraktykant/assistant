<?php

namespace App\Helpers\Scrapers;


use Carbon\Carbon;
use Exception;
use SimpleXLSX;

class DividendChampions implements IScraper
{
    private const URL = 'https://www.dripinvesting.org/tools/U.S.DividendChampions.xlsx';

    public function __construct(string $tickers = '')
    {
    }

    function getData(): array
    {
        $xlsx = $this->getXlsx();
        $allCCC = $this->getAllCCCSheet($xlsx);
        $headers = $this->createHeaders($allCCC);
        $spreadsheetDate = $this->getDate($allCCC);
        return $this->keyByHeaders($allCCC, $headers, $spreadsheetDate);
    }

    private function getXlsx() : SimpleXLSX
    {
        $xlsx =  file_get_contents(self::URL);
        if ($xlsx = SimpleXLSX::parse($xlsx, true)) {
            return $xlsx;
        } else {
            throw new Exception(SimpleXLSX::parseError());
        }
    }

    private function getAllCCCSheet(SimpleXLSX $xlsx)
    {
        $sheetNames = array_map('strtolower', $xlsx->sheetNames());

        if ($allCCCIndex = array_search('all ccc', $sheetNames)) {
            return $allCCCSheet = $xlsx->rows($allCCCIndex);
        } else {
            throw new Exception("Couldn't find All CCC sheet in spreadsheet");
        }
    }

    private function createHeaders($allCCC)
    {
        $h1 = $allCCC[4];
        $h2 = $allCCC[5];

        if (count($h1) != count($h2)) {
            throw new Exception('Rows 5 and 6 have different length');
        }

        $headers = [];

        for($i = 0; $i < count($h1); $i++) {

            $headers[$i] = ltrim($h1[$i] . " " . $h2[$i]);

            if (strpos($headers[$i],'Notes')) {
                $headers[$i] = 'Notes';
                continue;
            }

            if (strpos($headers[$i],'Price')) {
                $headers[$i] = 'Price';
                continue;
            }

        }
        return $headers;
    }

    private function cleanHeaders($headers)
    {

    }

    private function getDate($allCCC)
    {
        return Carbon::create(str_replace('As of', '', $allCCC[2][0]));
    }

    private function keyByHeaders(array $allCCC, array $headers, Carbon $date)
    {
        $output = [];
        $row_number = 0;
        $execute = 0;

        foreach ($allCCC as $row) {

            if ($row[0] == '' && $execute === 1) {
                break;
            }

            if ($execute === 1) {

                if (count($headers) != count($row)) {
                    print_r("Row number $row_number has unexpected length");
                }

                for ($i = 0; $i < count($row); $i++) {

                    $row[$i] = ($row[$i] === 'n/a') ? null : $row[$i];
                    $row[$i] = ($row[$i] === '') ? null : $row[$i];

                    $companyNameIndex = array_search('Company Name', $headers);
                    $companyName = $row[$companyNameIndex];
                    $output[$companyName][$headers[$i]] = $row[$i];
                    $output[$companyName]['Date'] = $date;
                }
            }
            /*
             * skip all rows before row with Name
             */
            if ($row[0] === 'Name') {
                $execute += 1;
            }
        }
        return $output;
    }
}