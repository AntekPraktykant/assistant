<?php

namespace App\Console\Commands;

use App\ExDividend;
use App\Helpers\Scrapers\Yahoo;
use App\Skrocona;
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
    public function handle()
    {
        $today = Carbon::today();
        $exdd = new ExDividend();
//        $exdd->date = $today;
        $all = Skrocona::all()->pluck('TICKER')->toArray();
        $yahoo = new Yahoo(array_slice($all, -3));
        $data = $yahoo->getData();
//dd($data);
        foreach ($data as $ticker => $dates) {
            $exdd->date = $today;
            $exdd->ex_date = empty($dates['exdd'][0]) ? null : $dates['exdd'][0];
            $exdd->ticker = $ticker;
            $exdd->save();
        }

    }
}
