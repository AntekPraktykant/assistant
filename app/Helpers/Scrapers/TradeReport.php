<?php

namespace App\Helpers\Scrapers;


class TradeReport
{
    /*
     * takes string containing one TradeReport html
     * returns array with option transactions from that file indexed by currency code
     */
    public function scrapSingleReport(string $fileContents) : array
    {
        $htmlTable = $this->extractOptionTable($fileContents);
        $fxArray = $this->separateFX($htmlTable);
        return $this->removeHTMLNoise($fxArray);
    }
    /*
     * takes array of fileContents
     * returns array with option transactions from that files indexed by currency code
     */
    public function scrapMultipleReports(array $files) : array
    {
        $output = [];

        foreach ($files as $fileContents) {
            $output = array_merge_recursive($output, $this->scrapSingleReport($fileContents));
        }

        return $output;
    }

    private function extractOptionTable($fileContents)
    {
        /*
         * extract option transactions table - start
         */
        $htmlTable = explode('Equity and Index Options - Held', $fileContents);
        /*
         * extract option transactions table - end
         */
        $htmlTable = explode('Financial Instrument Information', $htmlTable[1]);

        return $htmlTable[0];
    }
    /*
     * accepts html table with option transactions as string
     * returns array of html elements indexed by fx code
     */
    private function separateFX(string $htmlTable)
    {
        /*
         * extract different currencies
         * row with currency code has align="left" valign="middle" colspan="13">
         */
        $htmlArray = explode('align="left" valign="middle" colspan="13">', $htmlTable);
        unset($htmlArray[0]);

        $fxArray = [];

        foreach ($htmlArray as $row) {
            $fxCode = substr($row, 0, 3);
            $fxArray[$fxCode] = explode("<tbody", $row);
        }

        return $fxArray;
    }

    private function removeHTMLNoise($fxArray)
    {
        /*
         * clean transactions to get only row with actual transactions
         * currently row with class "row-summary no-details" contains actual data
         */
        foreach ($fxArray as $fxCode => &$htmlArray) {
            foreach ($htmlArray as $number => &$row) {
                if (!strpos($row, "row-summary no-details")) {
                    unset($htmlArray[$number]);
                } else {
                    $row = explode('</td>', $row);

                    foreach ($row as &$value) {
                        $value = str_replace(PHP_EOL, "", $value);
                        /*
                         * remove any td/tr html tags with attributes from the beginning of the string
                         */
                        $value = preg_replace('@^(\<|\>)[a-z0-9\s"\<\>=-]{2,}\>@', '', $value);
                    }
                    unset($row[count($row) - 1]);
                }
            }
        }
        return $fxArray;
    }
}