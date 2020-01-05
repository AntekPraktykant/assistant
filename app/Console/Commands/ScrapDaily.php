<?php

namespace App\Console\Commands;

use App\EarningsExpectation;
use App\EarningsSurprise;
use App\Helpers\Scrapers\YahooScrapper;
use App\Skrocona;
use App\YahooDaily;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScrapDaily extends Command
{
    const CCC_STOCKS_TABLE = 'skrocona';
    private $noOfScraps;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:daily {ticker=A}';

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
        $first = $this->argument('ticker') ?? 'A';
        $last = skrocona::max('date');
        $allStocks = Skrocona::where('date', '=', $last)->pluck('TICKER')->toArray();
        $noOfScraps = count($allStocks);
        $statuses = [];

        $execute = false;

        foreach ($allStocks as $stock) {

            if ($stock === $first) {
                $execute = true;
            }
//            elseif ($stock === 'DHIL')
//            {
//                $execute = false;
//                break;
//            }
            if ($execute) {
                if ($sleep > 0) {
                    sleep($sleep);
                    set_time_limit(30);
                }
                print_r(PHP_EOL . '----------------------------------' . $stock . ' -----------------------------------------' . PHP_EOL);
                $yahoo = new YahooScrapper($stock);

                try {
                    $stockData = $yahoo->getData();
                } catch (\Exception $e) {
                    sleep(10);
                    set_time_limit(30);
                    $this->handle(0.5, $stock);
                }

                $this->saveToDb($yahoo->getStockData(), $stock);
            }
        }

        print_r(PHP_EOL);
        print_r($statuses);
    }

    private function saveToDb($stockData, $ticker)
    {
        $today = Carbon::today('America/New_York');
        $yahooDaily = new YahooDaily();

        $yahooDaily->date = $today;
        $yahooDaily->ticker = $ticker;
        /*
         * change on 2019-12-13 Beta 3Y Monthly => Beta 5Y Monthly (yahoo change)
         */
        $yahooDaily->beta = $stockData['Beta 5Y Monthly'];
        $yahooDaily->earnings_1 = $stockData['Earnings Date 1'];
        $yahooDaily->earnings_2 = $stockData['Earnings Date 2'];
        $yahooDaily->ex_date = $stockData['Ex-Dividend Date'];
        $yahooDaily->mktcap = $stockData['Market Cap'];
        $yahooDaily->pe = $stockData['PE Ratio TTM'];
        $yahooDaily->close = $stockData['Last price'];
        $yahooDaily->day_min = $stockData["Day's Range 1"];
        $yahooDaily->day_max = $stockData["Day's Range 2"];
        $yahooDaily->year_min = $stockData["52 Week Range 1"];
        $yahooDaily->year_max = $stockData["52 Week Range 2"];
        $yahooDaily->eps_ttm = $stockData["EPS TTM"];
        $yahooDaily->analyst_1y_price_est = $stockData["1y Target Est"];
        $yahooDaily->forward_dividend = $stockData["Forward Dividend"];
        $yahooDaily->yield = empty($x = str_replace('%', '', $stockData["Yield"])) ? null : $x;

        if ($yahooDaily->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving exdd ' . $ticker . ": $yahooDaily->ex_date" . PHP_EOL);
            $yahooDaily->save();
        }

        if (isset($stockData['earnings_surprise'])) {
            $earnings = $stockData['earnings_surprise'];
            foreach ($quarterly = $earnings->quarterly as $quarter) {
                $earningsSurprise = new EarningsSurprise();
                $earningsSurprise->symbol = $ticker;
                $earningsSurprise->quarter = $quarter->date;
                if(! isset($quarter->actual->fmt)) {
                    continue;
                }
                $earningsSurprise->actual = $quarter->actual->fmt;
                if(! isset($quarter->estimate->fmt)) {
                    continue;
                }
                $earningsSurprise->estimate = $quarter->estimate->fmt;

                if ($earningsSurprise->where([['symbol', '=', $ticker], ['quarter', '=', $earningsSurprise->quarter]])->get()->isEmpty()) {
                    $earningsSurprise->save();
                }
            }

            if (isset($earnings->currentQuarterEstimateDate)) {
                $earningsExpectations = new EarningsExpectation();

                $earningsExpectations->symbol = $ticker;
                $earningsExpectations->quarter = $earnings->currentQuarterEstimateDate . $earnings->currentQuarterEstimateYear;
                $earningsExpectations->expectations = $earnings->currentQuarterEstimate->fmt ?? null;

                if ($earningsExpectations->where([
                    ['symbol', '=', $ticker],
                    ['quarter', '=', $earningsExpectations->quarter],
                    ['created_at', '=', $today]
                ])->get()->isEmpty()) {
                    $earningsExpectations->save();
                }
            }
        }

//        die();


//        $yahooPages = new YahooPages();
//        $yahooPages->date = $today;
//        $yahooPages->ticker = $ticker;
//        $yahooPages->page_contents = $yahoo->getPageContents();
//
//        if ($yahooPages->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
//            print_r('Saving page ' . $ticker . ": $yahooPages->ticker" . PHP_EOL);
//            $yahooPages->save();
//        }

        print_r(PHP_EOL . '==================================' . $ticker . ' complete =================================' . PHP_EOL);
    }
}
