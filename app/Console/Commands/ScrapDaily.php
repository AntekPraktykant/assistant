<?php

namespace App\Console\Commands;

use App\Helpers\Scrapers\Yahoo;
use App\Skrocona;
use App\YahooDaily;
use App\YahooPages;
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
    public function handle($sleep = 0.5, $first = 'A')
    {
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
                if ($sleep > 0 ) {
                    sleep($sleep);
                    set_time_limit(30);
                }
                print_r(PHP_EOL . '----------------------------------' . $stock . ' -----------------------------------------' . PHP_EOL);
                $yahoo = new Yahoo($stock);

                try {
                    $stockData = $yahoo->getData();
                } catch (\Exception $e) {
                    sleep(10);
                    set_time_limit(30);
                    $this->handle(0.5, $stock);
                }

                $this->saveToDb($yahoo, $stock);
            }
        }

        print_r(PHP_EOL);
        print_r($statuses);
    }

    private function saveToDb($yahoo, $ticker)
    {
        $values = $yahoo->getStockData();
        $today = Carbon::today('America/New_York');

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
        $yahooDaily->eps_ttm = $values["EPS TTM"];
        $yahooDaily->analyst_1y_price_est = $values["1y Target Est"];
        $yahooDaily->forward_dividend = $values["Forward Dividend"];
        $yahooDaily->yield = empty($x= str_replace('%', '', $values["Yield"])) ? null : $x;

        if ($yahooDaily->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving exdd ' . $ticker . ": $yahooDaily->ex_date" . PHP_EOL);
            $yahooDaily->save();
        }

        $yahooPages = new YahooPages();
        $yahooPages->date = $today;
        $yahooPages->ticker = $ticker;
        $yahooPages->page_contents = $yahoo->getPageContents();

        if ($yahooPages->where('date', $today->toDateString())->where('ticker', $ticker)->get()->isEmpty()) {
            print_r('Saving page ' . $ticker . ": $yahooPages->ticker" . PHP_EOL);
            $yahooPages->save();
        }

        print_r(PHP_EOL . '==================================' . $ticker . ' complete =================================' . PHP_EOL);
    }
}
