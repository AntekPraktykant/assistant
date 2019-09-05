<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Scrapers\Yahoo;

class HomeController extends Controller
{
    public function index()
    {
        $tickers = ['MSFT', 'JNJ', 'T', 'MA', 'AAPL', 'TSLA'];
        $yahoo = new Yahoo($tickers);
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
