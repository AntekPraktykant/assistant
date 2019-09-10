<?php

namespace App\Helpers\Scrapers;

interface IScraper
{
    function __construct(string $tickers);

    function getData() : array;
}