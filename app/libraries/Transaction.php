<?php

namespace App\libraries;


class Transaction
{
    private $symbol;
    private $tradeDate;
    private $settleDate;
    private $type;
    private $quantity;
    private $price;
    private $proceeds;
    private $commission;
    private $code;

    public $url;
    public $currency;

    /**
     * Transaction constructor.
     * @param $symbol
     * @param $tradeDate
     * @param $settleDate
     * @param $type
     * @param $quantity
     * @param $price
     * @param $proceeds
     * @param $commission
     * @param $code
     */
    public function __construct($symbol, $tradeDate, $settleDate, $type, $quantity, $price, $proceeds, $commission, $code, $currency)
    {
        $this->symbol = $symbol;
        $this->tradeDate = $tradeDate;
        $this->settleDate = $settleDate;
        $this->type = $type;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->proceeds = $proceeds;
        $this->commission = $commission;
        $this->code = $code;

        $this->currency;
        $this->url = (strtolower($currency) === 'cad') ? $this->symbol . 'to' : $this->symbol;
    }

}