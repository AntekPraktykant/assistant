<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $table = 'stocks_prices';
    protected $primaryKey = ['date', 'ticker'];
    public $incrementing = false;
}
