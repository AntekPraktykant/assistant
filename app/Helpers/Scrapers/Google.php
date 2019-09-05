<?php

namespace App\Helpers\Scrapers;


class Google implements IScraper
{
    public function __construct(array $tickers)
    {
        parent::__construct($tickers);
    }
    public function getData(): array
    {
        // TODO: Implement getData() method.
    }

}