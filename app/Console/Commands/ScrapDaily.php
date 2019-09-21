<?php

namespace App\Console\Commands;

use App\Beta;
use App\Earnings;
use App\ExDividend;
use App\Helpers\Scrapers\DividendChampions;
use App\Helpers\Scrapers\Yahoo;
use App\MarketCap;
use App\PERatio;
use App\Skrocona;
use App\StockPrice;
use App\YahooDaily;
use Carbon\Carbon;
use Illuminate\Console\Command;
use SimpleXLSX;

class ScrapDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scraps yahoo every day to get ccc stocks details';

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
    public function handle($sleep = 0.5)
    {
        $allStocks = Skrocona::all()->pluck('TICKER')->toArray();
        $statuses = [];

        $execute = true;

        foreach ($allStocks as $stock) {

//            if ($stock === 'TCF') {
//                $execute = true;
//            }
//            elseif ($stock === 'MAA')
//            {
//                $execute = false;
//                break;
//            }
            if ($execute) {
                if ($sleep > 0 ) {
                    sleep($sleep);
                    set_time_limit(30);
                }
                print_r(PHP_EOL . '----------------------------------' . $stock . ' -----------------------------------------' . PHP_EOL);
                $yahoo = new Yahoo($stock);
                $stockData = $yahoo->getData();
                $msg = $yahoo->getMessages();

                if (count($msg) > 1) {
                    $statuses[$stock] = $msg;
                }
                $this->saveToDb($stockData, $stock);
            }
        }
        print_r(PHP_EOL);
        print_r($statuses);
    }

    private function saveToDb($values, $ticker)
    {
        $today = Carbon::today('Europe/Warsaw');

        $yahooDaily = new YahooDaily();

        $yahooDaily->date = $today;
        $yahooDaily->ticker = $ticker;
        $yahooDaily->beta = $values['Beta 3Y Monthly'];
        $yahooDaily->earnings_1 = $values['Earnings Date 1'];
        $yahooDaily->earnings_2 = $values['Earnings Date 2'];
        $yahooDaily->ex_date = $values['Ex-Dividend Date'];
        $yahooDaily->mktcap = $values['Market Cap'];
        $yahooDaily->pe = $values['PE Ratio TTM'];
        $yahooDaily->close = $values['Last price'];
        $yahooDaily->day_min = $values["Day's Range 1"];
        $yahooDaily->day_max = $values["Day's Range 2"];
        $yahooDaily->year_min = $values["52 Week Range 1"];
        $yahooDaily->year_max = $values["52 Week Range 2"];

//        dd($yahooDaily);

//        $exdd = new ExDividend();
//        $earnings = new Earnings();
//        $beta = new Beta();
//        $peratio = new PERatio();
//        $stockprice = new StockPrice();
//        $mktcap = new MarketCap();
//
//        $exdd->date = $today;
//        $exdd->ex_date = $values['Ex-Dividend Date']; //isset($values['Ex-Dividend Date'][0]) ? $values['Ex-Dividend Date'][0] : null;
//        $exdd->ticker = $ticker;
//
//        $earnings->date = $today;
//        $earnings->earnings_1 = $values['Earnings Date 1']; //isset($values['Earnings Date 1'][0]) ? $values['earnings'][0] : null;
//        $earnings->earnings_2 = $values['Earnings Date 2']; //isset($values['earnings'][1]) ? $values['earnings'][1] : null;
//        $earnings->ticker = $ticker;
//
//        $beta->date = $today;
//        $beta->beta = $values['Beta 3Y Monthly'];//isset($values['beta'][0]) ? $values['beta'][0] : null;
//        $beta->ticker = $ticker;
//
//        $peratio->date = $today;
//        $peratio->pe = $values['PE Ratio TTM']; //isset($values['pe'][0]) ? $values['pe'][0] : null;
//        $peratio->ticker = $ticker;
//
//        $stockprice->date = $today;
//        $stockprice->close = $values['Last price'];
//        $stockprice->day_min = $values["Day's Range 1"];
//        $stockprice->day_max = $values["Day's Range 2"];
//        $stockprice->year_min = $values["52 Week Range 1"];
//        $stockprice->year_max = $values["52 Week Range 2"];
//        $stockprice->ticker = $ticker;
//
//        $mktcap->date = $today;
//        $mktcap->mktcap = $values['Market Cap']; //isset($values['mktcap'][0]) ? $values['mktcap'][0] : null;
//        $mktcap->ticker = $ticker;

        if ($yahooDaily->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving exdd ' . $ticker . ": $yahooDaily->ex_date" . PHP_EOL);
            $yahooDaily->save();
        }

//        if ($earnings->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//            print_r('Saving earnings ' . $ticker . ": $earnings->earnings_1 - $earnings->earnings_2" . PHP_EOL);
//            $earnings->save();
//        }
//
//        if ($beta->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//            print_r('Saving beta ' . $ticker . ": $beta->beta" . PHP_EOL);
//            $beta->save();
//        }
//
//        if ($peratio->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//            print_r('Saving peratio ' . $ticker . ": $peratio->pe" . PHP_EOL);
//            $peratio->save();
//        }
//
//        if ($stockprice->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//            print_r('Saving price ' . $ticker . ": $stockprice->close" . PHP_EOL);
//            $stockprice->save();
//        }
//
//        if ($mktcap->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//            print_r('Saving mktcap ' . $ticker . ": $mktcap->mktcap" . PHP_EOL);
//            $mktcap->save();
//        }

        print_r(PHP_EOL . '==================================' . $ticker . ' complete =================================' . PHP_EOL);
    }
}
