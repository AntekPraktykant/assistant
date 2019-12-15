<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 07.12.2019
 * Time: 17:06
 */

namespace App\Helpers\Scrapers\DTO;


class YahooScraperDTO
{
    private $data;
    private $today;
    private $ticker;
    private $beta3YMonthly;
    private $earningsDate1;
    private $earningsDate2;
    private $exDividendDate;
    private $marketCap;
    private $peRatioTTM;
    private $lastPrice;
    private $dailyRange1;
    private $dailyRange2;
    private $YearlyRange1;
    private $YearlyRange2;
    private $epsTTM;
    private $yearlyTargetEst;
    private $forwardDividend;
    private $yield;
    private $earningsSurprise = [];

    /**
     * @return array
     */
    public function getEarningsSurprise(): array
    {
        return $this->earningsSurprise;
    }

    /**
     * @param array $earningsSurprise
     */
    public function setEarningsSurprise(array $earningsSurprise): void
    {
        $this->earningsSurprise = $earningsSurprise;
    }
    /**
     * @return mixed
     */
    public function getToday()
    {
        return $this->today;
    }

    /**
     * @param mixed $today
     */
    public function setToday($today): void
    {
        $this->today = $today;
    }

    /**
     * @return mixed
     */
    public function getTicker()
    {
        return $this->ticker;
    }

    /**
     * @param mixed $ticker
     */
    public function setTicker($ticker): void
    {
        $this->ticker = $ticker;
    }

    /**
     * @return mixed
     */
    public function getBeta3YMonthly()
    {
        return $this->beta3YMonthly;
    }

    /**
     * @param mixed $beta3YMonthly
     */
    public function setBeta3YMonthly($beta3YMonthly): void
    {
        $this->beta3YMonthly = $beta3YMonthly;
    }

    /**
     * @return mixed
     */
    public function getEarningsDate1()
    {
        return $this->earningsDate1;
    }

    /**
     * @param mixed $earningsDate1
     */
    public function setEarningsDate1($earningsDate1): void
    {
        $this->earningsDate1 = $earningsDate1;
    }

    /**
     * @return mixed
     */
    public function getEarningsDate2()
    {
        return $this->earningsDate2;
    }

    /**
     * @param mixed $earningsDate2
     */
    public function setEarningsDate2($earningsDate2): void
    {
        $this->earningsDate2 = $earningsDate2;
    }

    /**
     * @return mixed
     */
    public function getExDividendDate()
    {
        return $this->exDividendDate;
    }

    /**
     * @param mixed $exDividendDate
     */
    public function setExDividendDate($exDividendDate): void
    {
        $this->exDividendDate = $exDividendDate;
    }

    /**
     * @return mixed
     */
    public function getMarketCap()
    {
        return $this->marketCap;
    }

    /**
     * @param mixed $marketCap
     */
    public function setMarketCap($marketCap): void
    {
        $this->marketCap = $marketCap;
    }

    /**
     * @return mixed
     */
    public function getPeRatioTTM()
    {
        return $this->peRatioTTM;
    }

    /**
     * @param mixed $peRatioTTM
     */
    public function setPeRatioTTM($peRatioTTM): void
    {
        $this->peRatioTTM = $peRatioTTM;
    }

    /**
     * @return mixed
     */
    public function getLastPrice()
    {
        return $this->lastPrice;
    }

    /**
     * @param mixed $lastPrice
     */
    public function setLastPrice($lastPrice): void
    {
        $this->lastPrice = $lastPrice;
    }

    /**
     * @return mixed
     */
    public function getDailyRange1()
    {
        return $this->dailyRange1;
    }

    /**
     * @param mixed $dailyRange1
     */
    public function setDailyRange1($dailyRange1): void
    {
        $this->dailyRange1 = $dailyRange1;
    }

    /**
     * @return mixed
     */
    public function getDailyRange2()
    {
        return $this->dailyRange2;
    }

    /**
     * @param mixed $dailyRange2
     */
    public function setDailyRange2($dailyRange2): void
    {
        $this->dailyRange2 = $dailyRange2;
    }

    /**
     * @return mixed
     */
    public function getYearlyRange1()
    {
        return $this->YearlyRange1;
    }

    /**
     * @param mixed $YearlyRange1
     */
    public function setYearlyRange1($YearlyRange1): void
    {
        $this->YearlyRange1 = $YearlyRange1;
    }

    /**
     * @return mixed
     */
    public function getYearlyRange2()
    {
        return $this->YearlyRange2;
    }

    /**
     * @param mixed $YearlyRange2
     */
    public function setYearlyRange2($YearlyRange2): void
    {
        $this->YearlyRange2 = $YearlyRange2;
    }

    /**
     * @return mixed
     */
    public function getEpsTTM()
    {
        return $this->epsTTM;
    }

    /**
     * @param mixed $epsTTM
     */
    public function setEpsTTM($epsTTM): void
    {
        $this->epsTTM = $epsTTM;
    }

    /**
     * @return mixed
     */
    public function getYearlyTargetEst()
    {
        return $this->yearlyTargetEst;
    }

    /**
     * @param mixed $yearlyTargetEst
     */
    public function setYearlyTargetEst($yearlyTargetEst): void
    {
        $this->yearlyTargetEst = $yearlyTargetEst;
    }

    /**
     * @return mixed
     */
    public function getForwardDividend()
    {
        return $this->forwardDividend;
    }

    /**
     * @param mixed $forwardDividend
     */
    public function setForwardDividend($forwardDividend): void
    {
        $this->forwardDividend = $forwardDividend;
    }

    /**
     * @return float|null
     */
    public function getYield()
    {
        return $this->yield;
    }

    /**
     * @param float|null $yield
     */
    public function setYield($yield): void
    {
        $yield = str_replace('%', '', $yield);
        $this->yield = $yield ?? null;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}