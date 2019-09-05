<?php

namespace App\Helpers\Scrapers;

interface IScraper
{
    function __construct(array $tickers);

    function getData() : array;
}