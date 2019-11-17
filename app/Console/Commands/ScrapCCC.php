<?php

namespace App\Console\Commands;

use App\Helpers\Scrapers\DividendChampions;
use App\Skrocona;
use Illuminate\Console\Command;

class ScrapCCC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:ccc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get contents of ccc list';

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
    public function handle()
    {
        $dc = new DividendChampions();
        $data = $dc->getData();

        foreach ($data as $stock) {
            $this->saveToDb($stock);
        }
    }

    private function saveToDb($stock)
    {
        $skrocona = new Skrocona();

        $skrocona->Date = $stock['Date'];
        $skrocona->CompanyName = $stock['Company Name'];
        $skrocona->Ticker = $stock['Ticker Symbol'];
        $skrocona->Industry = $stock['Industry'];
        $skrocona->Sector = $stock['Sector'];
        $skrocona->NoYrs = $stock['No. Yrs'];
        $skrocona->DivYield = $stock['Div. Yield'];
        $skrocona->EPSPayout = $stock['EPS% Payout'];
        $skrocona->PE = $stock['TTM P/E'];
        $skrocona->ROE = $stock['TTM ROE'];
        $skrocona->MktCap = $stock['MktCap ($Mil)'];
        $skrocona->DebtEquity = $stock['Debt/ Equity'];
        $skrocona->DivGrowth5y = $stock['Past 5yr Growth'];
        $skrocona->YearlyDividend = $stock['Annualized'];
        $skrocona->payouts = $stock['Payouts/ Year'];

        if ($skrocona->where('date', $stock['Date'])->where('Ticker', $stock['Ticker Symbol'])->get()->isEmpty()) {
            $skrocona->save();
        }
    }
}
