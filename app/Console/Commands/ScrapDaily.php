<?php

namespace App\Console\Commands;

use App\Beta;
use App\Earnings;
use App\ExDividend;
use App\Helpers\Scrapers\Yahoo;
use App\MarketCap;
use App\PERatio;
use App\Skrocona;
use App\StockPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScrapDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ScrapDaily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle($sleep = 1)
    {
        $allStocks = Skrocona::all()->pluck('TICKER')->toArray();
        $execute = false;
        foreach ($allStocks as $stock) {
//            if ($sleep > 0 ) {
//                sleep($sleep);
//                set_time_limit(30);
//            }
            if ($stock === 'TTEK') {
                $execute = true;
            }
            if ($execute) {
                print_r(PHP_EOL . '----------------------------------' . $stock . ' -----------------------------------------' . PHP_EOL);
                $yahoo = new Yahoo($stock);
                $stockData = $yahoo->getData();
                $this->saveToDb($stockData, $stock);
            }
        }

//        foreach ($data as $ticker => $values) {
//
//            $exdd = new ExDividend();
//            $earnings = new Earnings();
//            $beta = new Beta();
//            $peratio = new PERatio();
//            $stockprice = new StockPrice();
//            $mktcap = new MarketCap();
//
//            $exdd->date = $today;
//            $exdd->ex_date = isset($values['exdd'][0]) ? $values['exdd'][0] : null;
//            $exdd->ticker = $ticker;
//
//            $earnings->date = $today;
//            $earnings->earnings_1 = isset($values['earnings'][0]) ? $values['earnings'][0] : null;
//            $earnings->earnings_2 = isset($values['earnings'][1]) ? $values['earnings'][1] : null;
//            $earnings->ticker = $ticker;
//
//            $beta->date = $today;
//            $beta->beta = isset($values['beta'][0]) ? $values['beta'][0] : null;
//            $beta->ticker = $ticker;
//
//            $peratio->date = $today;
//            $peratio->pe = isset($values['pe'][0]) ? $values['pe'][0] : null;
//            $peratio->ticker = $ticker;
//
//            $stockprice->date = $today;
//            $stockprice->price = isset($values['price'][0]) ? $values['price'][0] : null;
//            $stockprice->ticker = $ticker;
//
//            $mktcap->date = $today;
//            $mktcap->mktcap = isset($values['mktcap'][0]) ? $values['mktcap'][0] : null;
//            $mktcap->ticker = $ticker;
//
//            if ($exdd->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//                print_r('Saving exdd ' . $ticker . PHP_EOL);
//                $exdd->save();
//            }
//
//            if ($earnings->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//                print_r('Saving earnings ' . $ticker . PHP_EOL);
//                $earnings->save();
//            }
//
//            if ($beta->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//                print_r('Saving beta ' . $ticker . PHP_EOL);
//                $beta->save();
//            }
//
//            if ($peratio->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//                print_r('Saving peratio ' . $ticker . PHP_EOL);
//                $peratio->save();
//            }
//
//            if ($stockprice->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//                print_r('Saving price ' . $ticker . PHP_EOL);
//                $stockprice->save();
//            }
//
//            if ($mktcap->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//                print_r('Saving mktcap ' . $ticker . PHP_EOL);
//                $mktcap->save();
//            }
//        }
    }

    private function saveToDb($values, $ticker)
    {
        $yesterday = Carbon::yesterday('Europe/Warsaw');

        $exdd = new ExDividend();
        $earnings = new Earnings();
        $beta = new Beta();
        $peratio = new PERatio();
        $stockprice = new StockPrice();
        $mktcap = new MarketCap();

        $exdd->date = $yesterday;
        $exdd->ex_date = $values['Ex-Dividend Date']; //isset($values['Ex-Dividend Date'][0]) ? $values['Ex-Dividend Date'][0] : null;
        $exdd->ticker = $ticker;

        $earnings->date = $yesterday;
        $earnings->earnings_1 = $values['Earnings Date 1']; //isset($values['Earnings Date 1'][0]) ? $values['earnings'][0] : null;
        $earnings->earnings_2 = $values['Earnings Date 2']; //isset($values['earnings'][1]) ? $values['earnings'][1] : null;
        $earnings->ticker = $ticker;

        $beta->date = $yesterday;
        $beta->beta = $values['Beta 3Y Monthly'];//isset($values['beta'][0]) ? $values['beta'][0] : null;
        $beta->ticker = $ticker;

        $peratio->date = $yesterday;
        $peratio->pe = $values['PE Ratio TTM']; //isset($values['pe'][0]) ? $values['pe'][0] : null;
        $peratio->ticker = $ticker;

        $stockprice->date = $yesterday;
        $stockprice->close = $values['Previous Close'];
        $stockprice->day_min = $values["Day's Range 1"];
        $stockprice->day_max = $values["Day's Range 2"];
        $stockprice->year_min = $values["52 Week Range 1"];
        $stockprice->year_max = $values["52 Week Range 2"];
        $stockprice->ticker = $ticker;

        $mktcap->date = $yesterday;
        $mktcap->mktcap = $values['Market Cap']; //isset($values['mktcap'][0]) ? $values['mktcap'][0] : null;
        $mktcap->ticker = $ticker;

        if ($exdd->where('date', $yesterday->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving exdd ' . $ticker . ": $exdd->ex_date" . PHP_EOL);
            $exdd->save();
        }

        if ($earnings->where('date', $yesterday->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving earnings ' . $ticker . ": $earnings->earnings_1" . PHP_EOL);
            $earnings->save();
        }

        if ($beta->where('date', $yesterday->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving beta ' . $ticker . ": $beta->beta" . PHP_EOL);
            $beta->save();
        }

        if ($peratio->where('date', $yesterday->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving peratio ' . $ticker . ": $peratio->pe" . PHP_EOL);
            $peratio->save();
        }

        if ($stockprice->where('date', $yesterday->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving price ' . $ticker . ": $stockprice->close" . PHP_EOL);
            $stockprice->save();
        }

        if ($mktcap->where('date', $yesterday->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving mktcap ' . $ticker . ": $mktcap->mktcap" . PHP_EOL);
            $mktcap->save();
        }

        print_r(PHP_EOL . '==================================' . $ticker . ' complete =================================' . PHP_EOL);
    }
}
