<?php

namespace App\Http\Controllers;

use App\Tag;
use App\TransactionStatuses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Transaction;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Scrapers\TradeReport;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
//        dd(Tag::all());

        $t = Transaction::find(1);

//        dd($t->transactionStatus->description);
//        dd(Transaction::where('matching_hash', '=', '111')->get());
        $this->createRollChain([]);
        dd($this->getChain(22));
//        dd(TransactionStatuses::all());
        dd(Transaction::first());//->transactionStatus->description);
        die();
        dd(Transaction::all()->where('expiry', '>', '2019-07-14')->first()->description);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
//        die('something stupid');
        return view('transactions/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function load(Request $request)
    {
        if ($request->method() == 'POST') {
            $files = [];
            foreach ($request->file('files') as $file) {
                $files[] = file_get_contents($file->path());
            }

            $tr = new TradeReport();
            $scrapedTransactions = $tr->scrapMultipleReports($files);
            $this->createRollChain($scrapedTransactions);
            $this->saveAll($tr->scrapMultipleReports($files));

            return redirect('transactions');
        }
        return view('transactions/load');
    }

    private function saveAll(array $array)
    {
        $duplicates = [];

        foreach ($array as $fx => $transactions) {
            foreach ($transactions as $t) {
                $transaction = new Transaction();
                $symbol = $this->processSymbol($t[1]);
                $transaction->symbol = $t[1];
                $transaction->underlying = $symbol[0];
                $transaction->trade_date = Carbon::parse($t[2]);
                $transaction->expiry = Carbon::parse($symbol[1]);
                $transaction->strike = $symbol[2];
                $transaction->option_type = $symbol[3];
                $transaction->transaction_type = $t[5];
                $transaction->quantity = $t[6];
                $transaction->price = str_replace(',', '', $t[7]);
                $transaction->proceeds = str_replace(',', '', $t[8]);
                $transaction->commission = $t[10];
                $transaction->code = $t[11];
                $transaction->user_id = 1;
                $transaction->currency = $fx;

                if (strpos($transaction->code, 'C') === false) {
                    $transaction->status_id = 1;
                } else {
                    $transaction->status_id = 2;
                }

                $transaction->matching_hash = hash('crc32',
                    $transaction->symbol .
                    $transaction->trade_date .
                    $transaction->proceeds .
                    $transaction->user_id .
                    $transaction->currency
                );

//                $transaction->matching_hash = hash('crc32',
//                    $transaction->quantity .
//                    $transaction->symbol .
//                    $transaction->option_type
//                );
//                dd($transaction->matching_hash);
                if (Transaction::where('matching_hash', '=', $transaction->matching_hash)->get()->count() != 0) {
                    $duplicates[] = $transaction;
                } else {
                    $transaction->save();
                }
            }
        }
        dd($duplicates);
    }

    private function processSymbol($symbol)
    {
        $symbol = explode(" ", $symbol);

        return $symbol;
    }

    private function createRollChain(array $transactionsByCurrency)
    {
        ($all = Transaction::orderBy('Symbol', 'DESC')->orderBy('expiry', 'ASC')->orderBy('trade_date', 'ASC')->get());
        ($all = $all->groupBy('symbol'));
        ($all->count());
        $this->rollEqualSymbols($all);
        $this->rollEqualTradeDates($all);

    }

    /*
     * find matches for transactions coming from file
     * match TradeDates and underlying with opposite status and quantity
     * $all is array with every new transaction
     * return $all without transactions which have both rolled_to and rolled_from not null
     */
    private function rollEqualTradeDates($all)
    {
        foreach ($all as $symbol_transaction) {
            foreach ($symbol_transaction as $transaction) {
                $tradeDate = $transaction->trade_date;
                $x = Transaction::where('trade_date', '=', $tradeDate)
                    ->where
                    ([
                        ['trade_date', '=', $tradeDate],
                        ['underlying', '=', $transaction->underlying],
                        ['quantity', '=', $transaction->quantity * (-1)],
                        ['status_id', '=', $this->getOppositeCode($transaction->status_id)],
                        ['id', '!=', $transaction->id],
                    ])
                    ->get();

                if ($x->count() > 1) {
                    die("Zbyt dużo dopasowań do $x");
                } elseif ($x->count() == 1) {
                    $x = $x[0];
                    if (strpos($transaction->code, 'O') !== false) {
                        $transaction->rolled_from = $x->id;
                        $x->rolled_to = $transaction->id;

                    } elseif (strpos($transaction->code, 'C') !== false) {
                        $x->rolled_from = $transaction->id;
                        $transaction->rolled_to = $x->id;
                    }
                    $x->save();
                    $transaction->save();
                }
            }
        }
    }

    /*
     * find matches for transactions coming from file
     * match symbols
     * $all is array with every new transaction
     * return $all without transactions which have both rolled_to and rolled_from not null
     */

    // and what if I do trade the same symbol twice during the day (like buy spy 300 P on different hours with diff qty) -> solved by adding qty??
    private function rollEqualSymbols($all)
    {
        foreach ($all as $symbol_transaction) {
            foreach ($symbol_transaction as $transaction) {
                $symbol = $transaction->symbol;
                $x = Transaction::
//                where('symbol', '=', $symbol)
                    where
                    ([
                        ['symbol', '=', $symbol],
                        ['status_id', '=', $this->getOppositeCode($transaction->status_id)],
                        ['quantity', '=', $transaction->quantity * (-1)],
                        ['id', '!=', $transaction->id],
                    ])
                    ->first();

                if (isset($x)) {
                    if (strpos($transaction->code, 'C') !== false) {
                        $transaction->rolled_from = $x->id;
                        $x->rolled_to = $transaction->id;
                    } elseif (strpos($transaction->code, 'O') !== false) {
                        $transaction->rolled_to = $x->id;
                        $x->rolled_from = $transaction->id;
                    }
                    $x->save();
                    $transaction->save();
                }
            }
        }
    }

    private function getOppositeCode($code)
    {
        switch ($code) {
            case 'C':
                return 'O';
            case 'O':
                return 'C';
            case 1:
                return 2;
            case 2:
                return 1;
        }
    }

    private function getChain($id)
    {
        $children = $this->getSubChain($id, 'children');
        $parents = $this->getSubChain($id);

        return array_merge($parents, [Transaction::find($id)], $children);
    }

    private function getChildren($id)
    {
        $chain = [];
        $id = Transaction::find($id)->rolled_to;
        while ($child = Transaction::find($id)) {
            $chain[] = $child;
            $id = $child->rolled_to;
        }

        return $chain;
    }

    private function getSubChain($id, $parentsOrChildren = 'parents')
    {
        $chain = [];
        $rolled = (strtolower($parentsOrChildren) === 'parents') ? 'rolled_from' : 'rolled_to';
        $id = Transaction::find($id)->$rolled;
        while ($next = Transaction::find($id)) {
            $chain[] = $next;
            $id = $next->$rolled;
        }

        return $chain;
    }
}
