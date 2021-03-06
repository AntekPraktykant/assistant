<?php

namespace App\Http\Controllers;

use App\Skrocona;
use Illuminate\Http\Request;
use App\Helpers\Scrapers\YahooScrapper;

class HomeController extends Controller
{
    public function index()
    {
        $tickers = 'cat';//[884 =>'MSFT', 0=>'JNJ', 5=>'T', 3=>'MA', 9=>'AAPL', 89=>'TSLA'];
        $yahoo = new YahooScrapper($tickers);
//        $scraper = new Yahoo($tickers);
        $results = $yahoo->getData();

//        dd($scraper->getData());
        dd($results);
        die();
    }

    public function about()
    {
        return view('home/about');
    }

    public function transactions()
    {

    }

}
